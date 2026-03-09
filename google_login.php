<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

/**
 * google_login.php
 */

require __DIR__ . '/vendor/autoload.php';

use Google\Client;

/* ===== สร้าง Client ===== */
$client = new Client();

/* ===== Google OAuth Config ===== */
$client->setClientId('GOOGLE_CLIENT_ID');
$client->setClientSecret('GOOGLE_SECRET');
$client->setRedirectUri('http://localhost/Expense_System/google_callback.php');

/* ===== Scope ===== */
$client->addScope('email');
$client->addScope('profile');

/* ===== CSRF ===== */
$state = bin2hex(random_bytes(16));
$_SESSION['oauth_state'] = $state;
$client->setState($state);

/* ===== Redirect ===== */
header('Location: ' . $client->createAuthUrl());
exit();