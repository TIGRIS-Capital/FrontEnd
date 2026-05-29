<?php
// Migration script to rename DB columns to _jnsa suffix.
// WARNING: Run this on your development database only and BACK UP your DB before running.
// Usage (CLI): php apply_rename_jnsa.php

// Try to include the project's DB connection file. Check common locations.
$included = false;
$candidates = [
    __DIR__ . '/../src/Naval_FinalsActivity3_DB.php',
    __DIR__ . '/../Naval_FinalsActivity3_DB.php',
    __DIR__ . '/Naval_FinalsActivity3_DB.php'
];
foreach ($candidates as $p) {
    if (file_exists($p)) { require_once $p; $included = true; break; }
}

if (!$included) {
    echo "Could not find Naval_FinalsActivity3_DB.php. Edit this script to point to your DB connection file.\n";
    exit(1);
}

function columnExists(mysqli $conn, string $table, string $column): bool
{
    $sql = "SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ? LIMIT 1";
    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        echo "Failed to prepare metadata lookup for {$table}.{$column}: ({$conn->errno}) {$conn->error}\n";
        return false;
    }

    $stmt->bind_param('ss', $table, $column);
    $stmt->execute();
    $stmt->store_result();
    $exists = $stmt->num_rows > 0;
    $stmt->close();

    return $exists;
}

function renameColumnIfNeeded(mysqli $conn, string $table, string $oldColumn, string $newColumn, string $definition): void
{
    $oldExists = columnExists($conn, $table, $oldColumn);
    $newExists = columnExists($conn, $table, $newColumn);

    if ($newExists) {
        echo "Skipping {$table}.{$newColumn}: target column already exists.\n";
        return;
    }

    if (!$oldExists) {
        echo "Skipping {$table}.{$oldColumn}: source column not found.\n";
        return;
    }

    $sql = sprintf('ALTER TABLE `%s` CHANGE `%s` `%s` %s', $table, $oldColumn, $newColumn, $definition);
    echo "Running: {$sql}\n";

    if ($conn->query($sql) === TRUE) {
        echo "OK\n";
    } else {
        echo "FAILED: ({$conn->errno}) {$conn->error}\n";
    }
}

$renames = [
    ['loan_member_jn', 'member_id', 'member_id_jnsa', 'INT(11) NOT NULL AUTO_INCREMENT'],
    ['loan_member_jn', 'member_name', 'member_name_jnsa', 'VARCHAR(255) NOT NULL'],
    ['loan_member_jn', 'contact_information', 'contact_information_jnsa', 'VARCHAR(50) NOT NULL'],
    ['loan_member_jn', 'address', 'address_jnsa', 'VARCHAR(255) NOT NULL'],
    ['loan_member_jn', 'member_img', 'member_img_jnsa', 'VARCHAR(255) NOT NULL'],
    ['loan_member_jn', 'username', 'username_jnsa', 'VARCHAR(255) NOT NULL'],
    ['loan_member_jn', 'password', 'password_jnsa', 'VARCHAR(255) NOT NULL'],
    ['loan_member_jn', 'user_type', 'user_type_jnsa', 'VARCHAR(20) NOT NULL'],
    ['loan_member_jn', 'otp_jn', 'otp_jnsa', 'VARCHAR(10) NULL'],
    ['loan_member_jn', 'user_status_jn', 'user_status_jnsa', "VARCHAR(20) NOT NULL DEFAULT 'Pending'"],

    ['loan_logs_jn', 'log_id', 'log_id_jnsa', 'INT(11) NOT NULL AUTO_INCREMENT'],
    ['loan_logs_jn', 'member_id', 'member_id_jnsa', 'INT(11) NOT NULL'],
    ['loan_logs_jn', 'action_jn', 'action_jnsa', 'VARCHAR(50) NOT NULL'],
    ['loan_logs_jn', 'datetime_jn', 'datetime_jnsa', 'DATETIME NOT NULL'],

    ['loan_type_jn', 'loan_type_id', 'loan_type_id_jnsa', 'INT(11) NOT NULL AUTO_INCREMENT'],
    ['loan_type_jn', 'loan_type_name', 'loan_type_name_jnsa', 'VARCHAR(100) NOT NULL'],
    ['loan_type_jn', 'description', 'description_jnsa', 'VARCHAR(255) NOT NULL'],
];

echo "About to evaluate " . count($renames) . " column renames.\n";
echo "Make sure you have a backup. Proceed? (yes/no): ";
$handle = fopen('php://stdin','r');
$line = trim(fgets($handle));
if (strtolower($line) !== 'yes') {
    echo "Aborted by user. No changes made.\n";
    exit;
}

foreach ($renames as [$table, $oldColumn, $newColumn, $definition]) {
    renameColumnIfNeeded($conn, $table, $oldColumn, $newColumn, $definition);
}

echo "Done. Please verify your schema and then run application tests.\n";

?>