<?php

class DBAccess
{
    protected static ?mysqli $connection = null;

    private function __construct()
    {
    }

    public static function connect(string $hostname, string $username, string $password, string $database): DBAccess
    {
        if (self::$connection instanceof mysqli) {
            return new self();
        }

        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
        $connection = new mysqli($hostname, $username, $password, $database);
        $connection->set_charset('utf8mb4');
        self::$connection = $connection;

        return new self();
    }

    public function select(string $query, ?array $binds): mysqli_result
    {
        $statement = self::$connection->prepare($query);

        if (!empty($binds)) {
            $statement->bind_param(implode(array_keys($binds)), ...array_values($binds));
        }

        $statement->execute();

        return $statement->get_result();
    }
}