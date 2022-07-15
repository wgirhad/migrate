<?php

namespace Wgirhad\Migrate\Filesystem;

use Wgirhad\Migrate\Migrate;
use Wgirhad\Migrate\MigrationResult;
use Iterator;
use IteratorAggregate;
use RuntimeException;
use ArrayIterator;

/**
 * @implements IteratorAggregate<File>
 **/
class FileCollection implements IteratorAggregate
{
    /**
     * @var array<File> $files
     **/
    protected array $files = [];
    protected Migrate $migrator;


    public function setMigrator(Migrate $migrator): void
    {
        $this->migrator = $migrator;
    }

    public function append(File $file): void
    {
        $this->files[] = $file;
    }

    public function filter(callable $filter): FileCollection
    {
        return self::fromArray(array_filter($this->files, $filter));
    }

    /**
     * @return Iterator<File>
     **/
    public function getIterator(): Iterator
    {
        return new ArrayIterator($this->files);
    }

    public function migrate(): MigrationResult
    {
        if (!isset($this->migrator)) {
            throw new RuntimeException(
                'setMigrator should be called before this method, ' .
                'or you could call FileCollection::migrateWith($migrator)'
            );
        }

        return $this->migrator->execute($this);
    }

    /**
     * @param array<File> $files
     **/
    public static function fromArray(array $files): self
    {
        $collection = new self();
        foreach ($files as $file) {
            $collection->append($file);
        }
        return $collection;
    }
}
