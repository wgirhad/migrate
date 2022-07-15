<?php

namespace Wgirhad\Migrate;

use Wgirhad\Migrate\Filesystem\DirectoryReader;
use Wgirhad\Migrate\Filesystem\FileCollection;
use Wgirhad\Migrate\Filesystem\File;
use Exception;
use PDO;

class Migrate
{
    protected Database $db;
    protected FileCollection $files;
    protected MigrationResult $result;

    /**
     * @var array<string, true> $previous_migrations
     */
    protected array $previous_migrations;

    public function setConnection(PDO $conn): self
    {
        $this->db = new Database($conn);
        return $this;
    }

    public function readDir(string $dir): FileCollection
    {
        $files = DirectoryReader::create($dir)->listFiles();
        $files->setMigrator($this);
        return $files;
    }

    public function execute(FileCollection $files): MigrationResult
    {
        $this->files = $files;
        $this->db->prepare();
        $this->filterMigrations();
        $this->executeMigrations();
        return $this->result;
    }

    protected function filterMigrations(): void
    {
        $this->indexMigrations();
        $this->filterMigratedFiles();
    }

    protected function indexMigrations(): void
    {
        $this->previous_migrations = [];
        foreach ($this->db->fetchMigrations() as $migration) {
            $hash = $this->hashMigration($migration);
            $this->previous_migrations[$hash] = true;
        }
    }

    protected function filterMigratedFiles(): void
    {
        $this->files = $this->files->filter($this->isMigrationPending(...));
    }

    public function isMigrationPending(File $file): bool
    {
        $hash = $this->hashFile($file);
        return !isset($this->previous_migrations[$hash]);
    }

    protected function hashMigration(Migration $migration): string
    {
        $name = $migration->filename;
        $sha1 = $migration->sha1;
        $hash = "{$name}.{$sha1}";
        return $hash;
    }

    protected function hashFile(File $file): string
    {
        $name = $file->basename;
        $sha1 = $file->sha1();
        $hash = "{$name}.{$sha1}";
        return $hash;
    }

    protected function executeMigrations(): void
    {
        $this->result = new MigrationResult();
        foreach ($this->files as $file) {
            try {
                $this->doMigrate($file);
            } catch (Exception $e) {
                $this->result->appendFailure($file, $e);
            }
        }
    }

    protected function doMigrate(File $file): void
    {
        $this->db->exec($file->read());
        $this->log($file);
        $this->result->appendSuccess($file);
    }

    protected function log(File $file): void
    {
        $migration = new Migration();
        $migration->filename = $file->basename;
        $migration->sha1 = $file->sha1();
        $this->db->logMigration($migration);
    }

    public static function create(): self
    {
        return new self();
    }
}
