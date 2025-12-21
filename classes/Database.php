<?php

namespace InvoiceApi\Classes;

use PDO;
use PDOException;

class Database
{
    private static ?PDO $connection = null;
    private array $config;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public function getConnection(): PDO
    {
        if (self::$connection === null) {
            try {
                $dsn = sprintf(
                    'mysql:host=%s;port=%s;dbname=%s;charset=%s',
                    $this->config['host'],
                    $this->config['port'],
                    $this->config['name'],
                    $this->config['charset']
                );

                self::$connection = new PDO(
                    $dsn,
                    $this->config['username'],
                    $this->config['password'],
                    [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                        PDO::ATTR_EMULATE_PREPARES => false,
                    ]
                );
            } catch (PDOException $e) {
                throw new \RuntimeException('Database connection failed: ' . $e->getMessage());
            }
        }

        return self::$connection;
    }
}
