<?php

namespace Wgirhad\Migrate\Filesystem;

use RuntimeException;

class File
{
    public readonly string $dirname;
    public readonly string $basename;

    public function __construct(
        public readonly string $filename
    ) {
        if (!is_file($this->filename)) {
            throw new RuntimeException("$filename is not a valid filename");
        }

        $this->dirname = dirname($this->filename);
        $this->basename = basename($this->filename);
    }

    public function sha1(): string
    {
        $result = sha1_file($this->filename, false);
        if ($result === false) {
            throw new RuntimeException("Couldn't read $this->filename");
        }
        return $result;
    }

    public static function fromDir(string $dirname, string $basename): self
    {
        $ds = DIRECTORY_SEPARATOR;
        $filename = rtrim($dirname, $ds) . $ds . ltrim($basename, $ds);
        return new self($filename);
    }

    public function read(): string
    {
        $result = file_get_contents($this->filename);
        if ($result === false) {
            throw new RuntimeException("Couldn't read $this->filename");
        }
        return $result;
    }
}
