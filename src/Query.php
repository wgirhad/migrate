<?php

namespace Wgirhad\Migrate;

use RuntimeException;
use PDO;

class Query
{
    public function __construct(
        public readonly Driver $driver
    ) {
    }

    public static function fromConnection(PDO $conn): self
    {
        $driverName = strval($conn->getAttribute(PDO::ATTR_DRIVER_NAME));
        return new self(Driver::from($driverName));
    }

    public function tableExists(string $tablename): string
    {
        return sprintf($this->readQuery('table_exists.sql'), $tablename);
    }

    public function createMigrationsTable(): string
    {
        return $this->readQuery('create_migrations_table.sql');
    }

    protected function readQuery(string $filename): string
    {
        $result = @file_get_contents(implode(DIRECTORY_SEPARATOR, [
            __DIR__,
            'sql',
            $this->driver->value,
            $filename,
        ]));

        if ($result === false) {
            throw new RuntimeException("Could not read $filename");
        }

        return $result;
    }
}
