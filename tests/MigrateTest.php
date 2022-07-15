<?php

declare(strict_types=1);

namespace Wgirhad\Migrate\Tests;

use PHPUnit\Framework\TestCase;
use PDO;
use PDOStatement;
use Wgirhad\Migrate\{
    Migrate,
    MigrationResult,
};

final class MigrateTest extends TestCase
{
    public function testMigrate(): void
    {
        $conn = new PDO("sqlite::memory:");
        $dir = __DIR__ . '/files';

        $result = $this->migrate($conn, $dir);
        $this->assertInstanceOf(MigrationResult::class, $result);
        $this->assertEquals(3, $result->count());
        $this->assertEquals(3, $result->successCount());
        $this->assertEquals(0, $result->failureCount());

        $result = $this->migrate($conn, $dir);
        $this->assertInstanceOf(MigrationResult::class, $result);
        $this->assertEquals(0, $result->count());
        $this->assertEquals(0, $result->successCount());
        $this->assertEquals(0, $result->failureCount());

        $query = $conn->query('select * from users');
        $this->assertInstanceOf(PDOStatement::class, $query);

        if ($query instanceof PDOStatement) {
            $users = $query->fetchAll(PDO::FETCH_ASSOC);

            $this->assertEquals([[
                'id' => 1,
                'name' => 'Foo',
            ]], $users);
        }

        $query = $conn->query('select * from migrations');
        $this->assertInstanceOf(PDOStatement::class, $query);

        if ($query instanceof PDOStatement) {
            $migrations = $query->fetchAll(PDO::FETCH_ASSOC);

            $this->assertEquals([
                [
                    'id' => 1,
                    'filename' => '00000_users.sql',
                    'sha1' => '6b0c450d9a60e0a08d9bf9eb46316af2a9761992',
                ],
                [
                    'id' => 2,
                    'filename' => '00001_users_data.sql',
                    'sha1' => '9585fb6282ebb3fa52481bc89e5153c7209e33ef',
                ],
                [
                    'id' => 3,
                    'filename' => '00002_deletion.sql',
                    'sha1' => '0ffef01398a4273dd3ca8c0ebace0a9b75330be8',
                ],
            ], $migrations);
        }
    }

    private function migrate(PDO $conn, string $dir): MigrationResult
    {
        return Migrate::create()
            ->setConnection($conn)
            ->readDir($dir)
            ->migrate();
    }
}
