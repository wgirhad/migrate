<?php

namespace Wgirhad\Migrate;

use RuntimeException;
use PDOStatement;
use Generator;
use PDO;

class Database
{
    public readonly Query $query;
    public readonly PDO $conn;
    private PDOStatement $prepared_log;

    public function __construct(PDO $conn)
    {
        $this->conn = $conn;
        $this->query = Query::fromConnection($conn);
    }

    public function prepare(): void
    {
        if (!$this->doesMigrationTableExist()) {
            $this->createMigrationsTable();
        }
    }

    protected function createMigrationsTable(): void
    {
        $query = $this->query->createMigrationsTable();
        $this->conn->exec($query);
    }

    protected function doesMigrationTableExist(): bool
    {
        $query = $this->query->tableExists('migrations');
        $query = $this->conn->query($query);

        if ($query !== false) {
            return false !== $query->fetch(PDO::FETCH_ASSOC);
        }

        return false;
    }

    /**
     * @return Generator<Migration>
     **/
    public function fetchMigrations(): Generator
    {
        $migrations = $this->conn->query('select * from migrations');

        if ($migrations === false) {
            throw new RuntimeException("Couldn't fetch data from the migrations table");
        }

        while ($migration = $migrations->fetchObject(Migration::class)) {
            // phpStan does not trust fetchObject
            if ($migration instanceof Migration) {
                yield $migration;
            }
        }
    }

    public function exec(string $query): void
    {
        $this->conn->exec($query);
    }

    public function logMigration(Migration $migration): void
    {
        if (!isset($this->prepared_log)) {
            $this->prepared_log = $this->conn->prepare('insert into migrations (filename, sha1) values (?, ?)');
        }

        $this->prepared_log->execute([
            $migration->filename,
            $migration->sha1,
        ]);
    }
}
