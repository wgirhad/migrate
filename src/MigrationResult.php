<?php

namespace Wgirhad\Migrate;

use Wgirhad\Migrate\Filesystem\File;
use Throwable;

class MigrationResult
{
    /**
     * @var array<int, array{file: string, status: bool, error: ?Throwable}> $results
     */
    private array $results = [];
    private int $success_count = 0;
    private int $failure_count = 0;

    public function appendFailure(File $file, Throwable $e): void
    {
        $this->results[] = [
            'file' => $file->basename,
            'status' => false,
            'error' => $e,
        ];

        $this->failure_count++;
    }

    public function appendSuccess(File $file): void
    {
        $this->results[] = [
            'file' => $file->basename,
            'status' => true,
            'error' => null,
        ];

        $this->success_count++;
    }

    public function count(): int
    {
        return $this->success_count + $this->failure_count;
    }

    public function successCount(): int
    {
        return $this->success_count;
    }

    public function failureCount(): int
    {
        return $this->failure_count;
    }

    /**
     * @return array<int, array{file: string, status: bool, error: ?Throwable}>
     */
    public function toArray(): array
    {
        return $this->results;
    }
}
