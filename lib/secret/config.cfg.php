<?php
/**
 * Created by PhpStorm.
 * User: Mitchel
 * Date: 3/19/15
 * Time: 10:49 AM
 */
global $pdo, $config;
//!IMPORTANT
require_once("password_compat.php");
//!IMPORTANT
// Database info
$config['database_name'] = 'forty2';
$config['database_host'] = 'localhost';
$config['database_user'] = 'root';
$config['database_pass'] = 'xMpATcMXEYb7zHkqjAatYRJQ';

// Create a database
try {
    $pdo = new PDO("mysql:host=" . $config['database_host'] . ";dbname=" . $config['database_name'] . ";", $config['database_user'], $config['database_pass']);
}catch(PDOException $e) {
    die("Unable to connect to database.");
}

// Load config from database
$query = $pdo->prepare("SELECT * FROM f2config");
$query->execute();
$results = $query->fetchAll();

foreach($results as $result) {
    $config[$result['config_name']] = $result['config_value'];
}