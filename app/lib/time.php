<?php
declare(strict_types=1);

/** Everything runs on the app clock (US Pacific by default). */
function now(): DateTimeImmutable
{
    return new DateTimeImmutable('now');
}

function today(): string
{
    return now()->format('Y-m-d');
}

function day(string $date): DateTimeImmutable
{
    return new DateTimeImmutable($date);
}

function add_days(string $date, int $days): string
{
    return day($date)->modify(($days >= 0 ? '+' : '') . $days . ' days')->format('Y-m-d');
}

/** Whole days from $a to $b (negative when $b is earlier). */
function days_between(string $a, string $b): int
{
    return (int) day(substr($a, 0, 10))->diff(day(substr($b, 0, 10)))->format('%r%a');
}

function month_start(?string $date = null): string
{
    return day($date ?? today())->format('Y-m-01');
}

function month_end(?string $date = null): string
{
    return day($date ?? today())->format('Y-m-t');
}

/** First day of last month and the same day of the month as today (capped at its last day). */
function last_month_to_date(): array
{
    $start = day(month_start())->modify('-1 month');
    $day = min((int) now()->format('j'), (int) $start->format('t'));
    return [$start->format('Y-m-d'), $start->format('Y-m-') . str_pad((string) $day, 2, '0', STR_PAD_LEFT)];
}

function second_clock(): DateTimeImmutable
{
    return now()->setTimezone(new DateTimeZone(setting('second_timezone', 'Asia/Karachi')));
}

function tz_short(string $tz): string
{
    return match ($tz) {
        'America/Los_Angeles' => 'PT',
        'Asia/Karachi' => 'PKT',
        default => (new DateTimeImmutable('now', new DateTimeZone($tz)))->format('T'),
    };
}

/** "9:40 PM", or "10 PM" on the hour when $short. */
function fmt_time(DateTimeInterface|string $t, bool $short = false): string
{
    $t = is_string($t) ? new DateTimeImmutable($t) : $t;
    return $short && $t->format('i') === '00' ? $t->format('g A') : $t->format('g:i A');
}

/** "9:30 to 11:00 PM", or "11:30 PM to 12:30 AM" across noon or midnight. */
function fmt_range(string $start, string $end): string
{
    $a = new DateTimeImmutable($start);
    $b = new DateTimeImmutable($end);
    $first = $a->format('A') === $b->format('A') ? $a->format('g:i') : $a->format('g:i A');
    return $first . ' to ' . $b->format('g:i A');
}

function fmt_day(?string $date): string
{
    return $date ? day($date)->format('j M') : 'Not set';
}

function fmt_long_day(string $date): string
{
    return day($date)->format('l, j M');
}

/** "1 h 30 m", "45 m", "2 h". */
function fmt_minutes(int $minutes): string
{
    $h = intdiv($minutes, 60);
    $m = $minutes % 60;
    return trim(($h ? "$h h " : '') . ($m || !$h ? "$m m" : ''));
}

function fmt_hours(float $hours): string
{
    return rtrim(rtrim(number_format($hours, 1), '0'), '.') . ' h';
}

/** Due date label and tone for a task, e.g. ["2 days late", "negative"]. */
function due_label(?string $due, ?string $time = null, bool $done = false): array
{
    if (!$due) {
        return ['No date', 'muted'];
    }
    $diff = days_between(today(), $due);
    $at = $time ? ', ' . fmt_time($due . ' ' . $time, true) : '';
    if ($done) {
        return [fmt_day($due), 'muted'];
    }
    return match (true) {
        $diff < -1 => [abs($diff) . ' days late', 'negative'],
        $diff === -1 => ['1 day late', 'negative'],
        $diff === 0 => ['Today' . $at, 'warning'],
        $diff === 1 => ['Tomorrow' . $at, 'default'],
        default => [fmt_day($due), 'default'],
    };
}

/** Morning, afternoon or evening greeting on the app clock. */
function greeting(): string
{
    $h = (int) now()->format('G');
    return $h < 12 ? 'Good morning' : ($h < 18 ? 'Good afternoon' : 'Good evening');
}
