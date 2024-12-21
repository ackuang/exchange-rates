<?php
// exchange-rates.php

header('Content-Type: application/json');

require_once 'config.php';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo json_encode(['error' => 'Database connection failed: ' . $e->getMessage()]);
    exit;
}

$date = isset($_GET['date']) ? $_GET['date'] : null;

try {
    if ($date) {
        $stmt = $pdo->prepare(
            "SELECT bc.code AS base_currency, tc.code AS target_currency, r.rate 
             FROM rates r
             JOIN currencies bc ON r.base_currency_id = bc.id
             JOIN currencies tc ON r.target_currency_id = tc.id
             WHERE r.effective_date = :date"
        );
        $stmt->execute(['date' => $date]);
    } else {
        $stmt = $pdo->prepare(
            "SELECT bc.code AS base_currency, tc.code AS target_currency, r.rate 
             FROM rates r
             JOIN currencies bc ON r.base_currency_id = bc.id
             JOIN currencies tc ON r.target_currency_id = tc.id
             WHERE r.effective_date = (SELECT MAX(effective_date) FROM rates)"
        );
        $stmt->execute();
    }

    $rates = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['rates' => $rates]);
} catch (PDOException $e) {
    echo json_encode(['error' => 'Query failed: ' . $e->getMessage()]);
}