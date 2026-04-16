<?php
// ================================================================
//  DAIRICH ICE CREAM — Database Connection
//  config/db.php
// ================================================================

function db(): PDO {
    static $pdo = null;

    if ($pdo === null) {
        $host     = 'localhost';
        $port     = '5432';
        $dbname   = 'dairich';
        $user     = 'postgres';      // your pgAdmin username
        $password = '';              // your pgAdmin password

        $pdo = new PDO(
            "pgsql:host=$host;port=$port;dbname=$dbname",
            $user,
            $password,
            [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]
        );
    }

    return $pdo;
}
?>