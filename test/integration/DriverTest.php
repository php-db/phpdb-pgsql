<?php

declare(strict_types=1);

namespace PhpDbIntegrationTest\Pgsql;

use PhpDb\Pgsql\Connection;
use PhpDb\Pgsql\Driver;
use PhpDbTestAsset\Pgsql\SetupTrait;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[Group('integration')]
#[CoversMethod(Driver::class, 'createStatement')]
final class DriverTest extends TestCase
{
    use SetupTrait;

    #[Test]
    public function createStatementBindsTheStatementToTheConnection(): void
    {
        $connection = new Connection($this->conn);
        $driver     = new Driver($connection);

        $statement = $driver->createStatement('SELECT name FROM test ORDER BY id');

        static::assertSame('foo', $statement->execute()->current()['name']);
    }
}
