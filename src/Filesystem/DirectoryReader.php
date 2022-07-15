<?php

namespace Wgirhad\Migrate\Filesystem;

use RuntimeException;

class DirectoryReader
{
    public function __construct(
        public readonly string $dir
    ) {
    }

    public function listFiles(): FileCollection
    {
        $entries = scandir($this->dir);
        if ($entries === false) {
            throw new RuntimeException("Couldn't scan $this->dir");
        }

        $entries = array_filter($entries, $this->isFile(...));
        $entries = array_map($this->getFileInst(...), $entries);
        return FileCollection::fromArray($entries);
    }

    public function getFileInst(string $filename): File
    {
        return File::fromDir($this->dir, $filename);
    }

    public function isFile(string $entry): bool
    {
        return is_file($this->dir . DIRECTORY_SEPARATOR . $entry);
    }

    public static function create(string $dir): self
    {
        return new self($dir);
    }
}
