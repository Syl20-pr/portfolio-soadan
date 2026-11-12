<?php
/**
 * =============================================================================
 *  Portfolio SKS — Helpers de compatibilité SQL (MySQL ↔ SQLite)
 * =============================================================================
 *
 *  Ces fonctions abstraient les différences de syntaxe SQL entre MySQL et SQLite
 *  afin que l'application tourne sans modification sur les deux moteurs.
 *
 *  Utilisation : inclus automatiquement depuis database.php.
 * =============================================================================
 */

declare(strict_types=1);

/**
 * Retourne l'expression SQL "maintenant moins N unités" pour le driver actif.
 *
 * @param int    $value  Valeur numérique (ex. 15, 1, 7)
 * @param string $unit   Unité MySQL : MINUTE | HOUR | DAY | ...
 */
function sql_now_minus(int $value, string $unit): string
{
    $driver = db_driver();
    if ($driver === 'sqlite') {
        // SQLite : datetime('now', '-15 minutes'), datetime('now', '-1 day'), etc.
        $unitMap = [
            'SECOND'  => 'seconds',
            'MINUTE'  => 'minutes',
            'HOUR'    => 'hours',
            'DAY'     => 'days',
            'MONTH'   => 'months',
            'YEAR'    => 'years',
        ];
        $sqliteUnit = $unitMap[strtoupper($unit)] ?? strtolower($unit);
        return "datetime('now', '-{$value} {$sqliteUnit}')";
    }
    // MySQL / MariaDB
    return "(NOW() - INTERVAL {$value} {$unit})";
}

/**
 * Retourne l'expression SQL pour l'horodatage actuel.
 */
function sql_now(): string
{
    return db_driver() === 'sqlite' ? "datetime('now')" : 'NOW()';
}

/**
 * Génère un INSERT upsert (INSERT … ON DUPLICATE KEY UPDATE pour MySQL,
 * INSERT OR REPLACE pour SQLite).
 *
 * @param string   $table   Nom de la table
 * @param string[] $cols    Colonnes à insérer
 * @param string[] $update  Colonnes à mettre à jour (MySQL seulement) — si vide, toutes sauf PK
 */
function sql_upsert(string $table, array $cols, array $update = []): string
{
    $driver      = db_driver();
    $placeholders = implode(', ', array_map(fn($c) => ":$c", $cols));
    $colList      = implode(', ', $cols);

    if ($driver === 'sqlite') {
        return "INSERT OR REPLACE INTO {$table} ({$colList}) VALUES ({$placeholders})";
    }

    // MySQL
    if (empty($update)) {
        $update = $cols;
    }
    $updateClause = implode(', ', array_map(fn($c) => "{$c} = VALUES({$c})", $update));
    return "INSERT INTO {$table} ({$colList}) VALUES ({$placeholders}) ON DUPLICATE KEY UPDATE {$updateClause}";
}

