<?php

declare(strict_types=1);

namespace App\Database;

use PDO;

class PDODatabaseConnection implements DatabaseConnectionInterface
{
    public function __construct(
        private readonly string $uri,
        private readonly string $user,
        private readonly string $password
    ) {
    }

    public function connect(): PDO
    {
        return new PDO($this->uri, $this->user, $this->password);
    }
}
