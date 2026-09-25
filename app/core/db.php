<?php
declare(strict_types=1);

function db(): PDO
{
    static $pdo = null;
    if ($pdo === null) {
        $c = config()['db'];
        // On Windows, "localhost" tries IPv6 first and waits about a second before falling back to MySQL
        // on IPv4, on every request. 127.0.0.1 connects straight away.
        $host = PHP_OS_FAMILY === 'Windows' && $c['host'] === 'localhost' ? '127.0.0.1' : $c['host'];
        $pdo = new PDO(
            "mysql:host={$host};dbname={$c['name']};charset=utf8mb4",
            $c['user'],
            $c['pass'],
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]
        );
    }
    return $pdo;
}

function q(string $sql, array $params = []): PDOStatement
{
    $stmt = db()->prepare($sql);
    $stmt->execute($params);
    return $stmt;
}

function rows(string $sql, array $params = []): array
{
    return q($sql, $params)->fetchAll();
}

function row(string $sql, array $params = []): ?array
{
    return q($sql, $params)->fetch() ?: null;
}

function val(string $sql, array $params = []): mixed
{
    $v = q($sql, $params)->fetchColumn();
    return $v === false ? null : $v;
}

/** Column names always come from code, never from user input. */
function insert(string $table, array $data): int
{
    $cols = array_keys($data);
    $sql = sprintf(
        'INSERT INTO %s (%s) VALUES (%s)',
        $table,
        implode(', ', $cols),
        implode(', ', array_map(fn ($c) => ":$c", $cols))
    );
    q($sql, $data);
    return (int) db()->lastInsertId();
}

function update(string $table, int $id, array $data): void
{
    if (!$data) {
        return;
    }
    $set = implode(', ', array_map(fn ($c) => "$c = :$c", array_keys($data)));
    q("UPDATE $table SET $set WHERE id = :id", $data + ['id' => $id]);
}

function delete_row(string $table, int $id): void
{
    q("DELETE FROM $table WHERE id = ?", [$id]);
}

/** Rewrites the position column so rows follow the given id order. */
function reorder(string $table, array $ids, array $extra = []): void
{
    $set = implode('', array_map(fn ($c) => ", $c = :$c", array_keys($extra)));
    $stmt = db()->prepare("UPDATE $table SET position = :pos$set WHERE id = :id");
    foreach (array_values($ids) as $i => $id) {
        $stmt->execute(['pos' => $i, 'id' => (int) $id] + $extra);
    }
}
