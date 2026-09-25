<?php
declare(strict_types=1);

/* Small HTML building blocks shared by the page templates. Every value is escaped here. */

function category_pill(?string $name, ?string $color, bool $withIcon = false, ?string $iconName = null): string
{
    if (!$name) {
        return '<span class="pill c-gray">No category</span>';
    }
    return '<span class="pill c-' . e($color ?: 'gray') . '">' . ($withIcon && $iconName ? icon($iconName, 14) : '') . e($name) . '</span>';
}

function priority_pill(string $priority): string
{
    return '<span class="pill prio-' . e($priority) . '">' . icon('flag', 14) . e(TASK_PRIORITIES[$priority] ?? $priority) . '</span>';
}

/**
 * Delta chip. The arrow shows direction; the tone shows good or bad.
 * $goodWhenUp: true for income, false for spend or pending tasks.
 */
function trend_chip(?float $change, string $text, bool $goodWhenUp = true): string
{
    if ($change === null) {
        return '';
    }
    if (abs($change) < 0.05) {
        return '<span class="chip tone-bg-muted">' . icon('minus', 14) . e($text) . '</span>';
    }
    $up = $change > 0;
    $good = $up === $goodWhenUp;
    return '<span class="chip ' . ($good ? 'tone-bg-positive' : 'tone-bg-negative') . '">'
        . icon($up ? 'trending-up' : 'trending-down', 14) . e($text) . '</span>';
}

function signed_pct(?float $change): string
{
    return $change === null ? '' : ($change >= 0 ? '+' : '') . number_format($change, 1) . '%';
}

/** Card title row: icon, title, optional count and tools on the right. */
function card_head(string $iconName, string $title, string $tools = '', ?int $count = null, string $tag = 'h2'): string
{
    return '<div class="card-head">' . icon($iconName, 18) . "<$tag>" . e($title) . "</$tag>"
        . ($count !== null ? '<em class="count">' . $count . '</em>' : '')
        . ($tools !== '' ? '<div class="card-head-tools">' . $tools . '</div>' : '') . '</div>';
}

function menu_button(string $id, string $label = 'More actions'): string
{
    return '<button class="icon-btn icon-btn-sm" type="button" data-menu="' . e($id) . '" aria-label="' . e($label) . '">' . icon('ellipsis', 18) . '</button>';
}

/** KPI card: title row, big number with a delta chip, footnote. */
function kpi_card(string $iconName, string $title, string $value, string $chip, string $foot, string $href = ''): string
{
    $tools = $href ? '<a class="icon-btn icon-btn-sm" href="' . e($href) . '" aria-label="Open ' . e($title) . '">' . icon('arrow-up-right', 16) . '</a>' : '';
    return '<section class="card kpi">' . card_head($iconName, $title, $tools, null, 'h3')
        . '<div class="panel"><div class="kpi-value"><span class="t-stat">' . e($value) . '</span>' . $chip . '</div>'
        . '<p class="kpi-foot">' . $foot . '</p></div></section>';
}

function empty_state(string $iconName, string $title, string $text, string $action = '', string $class = ''): string
{
    return '<div class="empty ' . e($class) . '"><span class="empty-icon">' . icon($iconName, 22) . '</span>'
        . '<h3 class="t-section">' . e($title) . '</h3><p>' . e($text) . '</p>' . $action . '</div>';
}

function due_html(string $text, string $tone): string
{
    $iconName = $tone === 'negative' ? 'calendar-x-2' : 'calendar';
    return '<span class="due tone-' . e($tone === 'warning' ? 'default' : $tone) . '">' . icon($iconName, 16) . e($text) . '</span>';
}

/** Status badge for goals, invoices and projects. */
function status_badge(string $text, string $tone): string
{
    return '<span class="pill tone-bg-' . e($tone) . '"><span class="dot dot-solid"></span>' . e($text) . '</span>';
}
