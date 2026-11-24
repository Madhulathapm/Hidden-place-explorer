<?php
/**
 * Database Connection Configuration
 * Hidden Places Explorer
 */

define('DB_NAME', getenv('DB_NAME') ?: 'hidden_places_explorer');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') !== false ? getenv('DB_PASS') : '');
define('DB_CHARSET', 'utf8mb4');

/**
 * Returns a shared PDO instance with automatic multi-port detection
 * Detects whether MySQL/MariaDB is listening on port 3307 or standard 3306
 * @return PDO
 */
function getDBConnection(): PDO {
    static $pdo = null;

    if ($pdo === null) {
        // Preferred port order: 3307 (XAMPP MariaDB on systems with MySQL80 service), 3306 (Standard XAMPP default)
        $configuredPort = getenv('DB_PORT');
        $portsToTry = $configuredPort ? [(int)$configuredPort] : [3307, 3306];
        $hostsToTry = ['127.0.0.1', 'localhost'];

        $lastException = null;

        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES " . DB_CHARSET . " COLLATE utf8mb4_unicode_ci"
        ];

        foreach ($portsToTry as $port) {
            foreach ($hostsToTry as $host) {
                try {
                    $dsn = "mysql:host={$host};port={$port};dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
                    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
                    return $pdo;
                } catch (PDOException $e) {
                    $lastException = $e;
                    // If error is 1049 (database doesn't exist yet), but server is reachable:
                    if ($e->getCode() == 1049) {
                        die(renderDatabaseSetupNotice("Database '" . DB_NAME . "' was not found on port {$port}. Please import <code>database.sql</code> into phpMyAdmin."));
                    }
                }
            }
        }

        // If all attempts failed, render friendly troubleshooting guide
        $errMsg = $lastException ? $lastException->getMessage() : "Could not connect to MySQL server on ports 3306 or 3307.";
        die(renderDatabaseSetupNotice($errMsg));
    }

    return $pdo;
}

/**
 * Friendly Setup Notice for XAMPP users
 */
function renderDatabaseSetupNotice(string $errorMessage): string {
    return <<<HTML
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Database Setup Required - Hidden Places Explorer</title>
        <style>
            body {
                font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
                background-color: #f7f4ea;
                color: #16382c;
                display: flex;
                align-items: center;
                justify-content: center;
                min-height: 100vh;
                margin: 0;
                padding: 20px;
                box-sizing: border-box;
            }
            .card {
                background: #ffffff;
                border: 1px solid #d4dfd9;
                border-top: 5px solid #2d6a4f;
                border-radius: 12px;
                padding: 35px;
                max-width: 600px;
                box-shadow: 0 10px 25px rgba(22, 56, 44, 0.1);
            }
            h2 { color: #1b4332; margin-top: 0; }
            p { line-height: 1.6; color: #3f5e51; }
            .alert {
                background: #fff3cd;
                border: 1px solid #ffeeba;
                color: #856404;
                padding: 12px 16px;
                border-radius: 6px;
                margin: 15px 0;
                font-family: monospace;
                word-break: break-all;
            }
            ol { padding-left: 20px; color: #244638; line-height: 1.8; }
            code {
                background: #eaf1ed;
                padding: 2px 6px;
                border-radius: 4px;
                color: #1b4332;
                font-weight: 600;
            }
            .btn {
                display: inline-block;
                margin-top: 15px;
                background: #2d6a4f;
                color: #ffffff;
                text-decoration: none;
                padding: 10px 20px;
                border-radius: 6px;
                font-weight: 600;
            }
            .btn:hover { background: #1b4332; }
        </style>
    </head>
    <body>
        <div class="card">
            <h2>🌍 Hidden Places Explorer: Database Notice</h2>
            <div class="alert">{$errorMessage}</div>
            <p>To initialize the database in XAMPP:</p>
            <ol>
                <li>Open <strong>XAMPP Control Panel</strong> and ensure <strong>Apache</strong> &amp; <strong>MySQL</strong> are running.</li>
                <li>Go to <a href="http://localhost/phpmyadmin" target="_blank"><strong>http://localhost/phpmyadmin</strong></a> in your browser.</li>
                <li>Click <strong>Import</strong> tab at the top.</li>
                <li>Choose the file <code>database.sql</code> located in the root of this project and click <strong>Go / Import</strong>.</li>
                <li>Reload this page!</li>
            </ol>
            <a href="javascript:location.reload();" class="btn">🔄 Retry Connection</a>
        </div>
    </body>
    </html>
HTML;
}
