<?php
declare(strict_types=1);

/*
 * Charts are rendered on the server as HTML and SVG, so they paint with the page and need no library.
 * JS only adds tooltips through data-tip. Styles live in app.css under "Charts".
 */

/** Round axis step, e.g. 778 becomes 1,000 and 230 becomes 250. */
function nice_step(float $value): float
{
    if ($value <= 0) {
        return 1;
    }
    $mag = 10 ** floor(log10($value));
    foreach ([1, 2, 2.5, 4, 5, 10] as $step) {
        if ($step * $mag >= $value) {
            return $step * $mag;
        }
    }
    return 10 * $mag;
}

/**
 * Vertical stacked bars with rounded segments.
 * $bars: [label, segments: [[value, class, name]], tip (html), state ('future'|'short'|''), short (value)]
 * $opts: max, ticks (count), format (callable), line ([value, label]), height (px), grouped (bars side by side)
 */
function bar_chart(array $bars, array $opts = []): string
{
    $format = $opts['format'] ?? 'usd_short';
    $totals = array_map(fn ($b) => array_sum(array_column($b['segments'], 0)) + ($b['short'] ?? 0), $bars);
    $ticks = $opts['ticks'] ?? 3;
    // 5% headroom so the tallest bar or the goal line never touches the top.
    $max = $opts['max'] ?? nice_step(max([...$totals, $opts['line'][0] ?? 0, 1]) * 1.05 / $ticks) * $ticks;
    $height = $opts['height'] ?? 200;

    $html = '<div class="vchart" style="--h:' . $height . 'px">';
    $html .= '<div class="vchart-axis">';
    for ($i = $ticks; $i >= 0; $i--) {
        $html .= '<span>' . e($format($max / $ticks * $i)) . '</span>';
    }
    $html .= '</div><div class="vchart-plot">';
    for ($i = $ticks; $i >= 0; $i--) {
        $html .= '<i class="vchart-grid" style="bottom:' . round($i / $ticks * 100, 3) . '%"></i>';
    }
    if (!empty($opts['line'])) {
        [$lineValue, $lineLabel] = $opts['line'];
        $html .= '<div class="vchart-line" style="bottom:' . round(min(1, $lineValue / $max) * 100, 3) . '%"><span>' . e($lineLabel) . '</span></div>';
    }
    $grouped = !empty($opts['grouped']);
    $html .= '<div class="vchart-bars">';
    foreach ($bars as $bar) {
        $state = $bar['state'] ?? '';
        $html .= '<div class="vchart-col' . ($state ? ' is-' . e($state) : '') . '"' . attrs(['data-tip' => $bar['tip'] ?? null]) . '>';
        $html .= '<div class="vchart-stack' . ($grouped ? ' is-grouped' : '') . '">';
        if ($state === 'future') {
            $html .= '<b class="seg seg-future" style="height:40%"></b>';
        }
        if (!empty($bar['short'])) {
            $html .= '<b class="seg seg-short" style="height:' . round($bar['short'] / $max * 100, 3) . '%"></b>';
        }
        // Stacks draw top segment first; grouped bars stand side by side in their given order.
        foreach ($grouped ? $bar['segments'] : array_reverse($bar['segments']) as [$value, $class]) {
            if ($value > 0 || $grouped) {
                $html .= '<b class="seg ' . e($class) . '" style="height:' . round($value / $max * 100, 3) . '%"></b>';
            }
        }
        $html .= '</div><span class="vchart-label">' . e($bar['label']) . '</span></div>';
    }
    return $html . '</div></div></div>';
}

/**
 * Donut with rounded, gapped segments.
 * $segments: [[value, color css, name]]
 */
function donut(array $segments, int $size = 180, int $stroke = 22, string $center = '', string $sub = ''): string
{
    $total = array_sum(array_column($segments, 0));
    $r = ($size - $stroke) / 2;
    $c = 2 * M_PI * $r;
    $gap = $stroke + 6;
    $svg = sprintf('<svg class="donut" width="%1$d" height="%1$d" viewBox="0 0 %1$d %1$d" role="img" aria-label="%2$s">', $size, e($center . ' ' . $sub));
    $svg .= sprintf('<circle cx="%1$s" cy="%1$s" r="%2$s" fill="none" stroke="var(--bg-muted)" stroke-width="%3$d" opacity="%4$s"/>', $size / 2, $r, $stroke, $total > 0 ? 0 : 1);
    $offset = 0.0;
    foreach ($segments as [$value, $color, $name]) {
        if ($total <= 0 || $value <= 0) {
            continue;
        }
        $len = $value / $total * $c;
        $visible = max(0.01, $len - $gap);
        $svg .= sprintf(
            '<circle cx="%1$s" cy="%1$s" r="%2$s" fill="none" stroke="%3$s" stroke-width="%4$d" stroke-linecap="round" stroke-dasharray="%5$.2f %6$.2f" stroke-dashoffset="%7$.2f" transform="rotate(-90 %1$s %1$s)" data-tip="%8$s"/>',
            $size / 2, $r, e($color), $stroke, $visible, $c - $visible, -($offset + $gap / 2),
            e('<b>' . e($name) . '</b> ' . num($value) . ' (' . pct($value, $total) . '%)')
        );
        $offset += $len;
    }
    $svg .= '</svg>';
    return '<div class="donut-wrap" style="--size:' . $size . 'px">' . $svg
        . ($center !== '' ? '<div class="donut-center"><b>' . e($center) . '</b><span>' . e($sub) . '</span></div>' : '') . '</div>';
}

/**
 * Horizontal stacked bar (Workload chart): [[value, class, name]] against $max.
 */
function hstack(array $segments, float $max): string
{
    $html = '<div class="hstack">';
    foreach ($segments as [$value, $class, $name]) {
        if ($value > 0) {
            $html .= '<b class="' . e($class) . '" style="flex-grow:' . round($value, 3) . '" data-tip="' . e('<b>' . e($name) . '</b> ' . num($value)) . '"></b>';
        }
    }
    $rest = $max - array_sum(array_column($segments, 0));
    if ($rest > 0) {
        $html .= '<i style="flex-grow:' . round($rest, 3) . '"></i>';
    }
    return $html . '</div>';
}

/** Rounded progress bar. $class sets the fill color, e.g. "c-purple". */
function progress_bar(int $percent, string $class = '', string $size = ''): string
{
    return '<div class="bar ' . e(trim($class . ' ' . $size)) . '" role="progressbar" aria-valuenow="' . $percent . '" aria-valuemin="0" aria-valuemax="100"><span style="--p:' . ($percent / 100) . '"></span></div>';
}

/** Four-segment progress used on kanban cards. */
function seg_bar(int $percent): string
{
    $on = (int) round($percent / 25);
    $html = '<div class="segbar" aria-hidden="true">';
    for ($i = 0; $i < 4; $i++) {
        $html .= '<span' . ($i < $on ? ' class="on"' : '') . '></span>';
    }
    return $html . '</div>';
}
