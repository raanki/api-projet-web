<?php

function getEnvVariables() {
    $envPath = __DIR__ . '/../.env.local';


    if (!file_exists($envPath)) {
        return;
    }


    $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if ($lines === false) {
        return;
    }

    foreach ($lines as $line) {
        if (strpos($line, '=') !== false) {
            list($name, $value) = explode('=', $line, 2);
            $name = trim($name);
            $value = trim($value);

            switch ($name) {
                case 'DB_HOST':
                    $dbHost = $GLOBALS['dbHost'] = $value;
                    break;
                case 'DB_DATABASE':
                    $dbName =  $GLOBALS['dbName'] = $value;
                    break;
                case 'DB_USERNAME':
                    $dbUsername= $GLOBALS['dbUsername'] = $value;
                    break;
                case 'DB_PASSWORD':
                    $dbPassword = $GLOBALS['dbPassword'] = $value;
                    break;
            }
        }
    }
}

getEnvVariables();

function connectDb() {
    $conn = new mysqli($GLOBALS['dbHost'], $GLOBALS['dbUsername'], $GLOBALS['dbPassword'], $GLOBALS['dbName']);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    return $conn;
}
