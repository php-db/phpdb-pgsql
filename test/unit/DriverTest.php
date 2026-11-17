<?php

declare(strict_types=1);

namespace PhpDbTest\Pgsql;

use PhpDb\Adapter\Exception;
use PhpDb\Pgsql\Connection;
use PhpDb\Pgsql\Driver;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[Group('unit')]
#[CoversMethod(Driver::class, 'createStatement')]
final class DriverTest extends TestCase
{
    #[Test]
    public function createStatementRejectsAConnectionWithoutAPgsqlResource(): void
    {
        $connection = $this->createStub(Connection::class);
        $connection->method('isConnected')->willReturn(true);
        $connection->method('getResource')->willReturn(null);

        $driver = new Driver($connection);

        $this->expectException(Exception\RuntimeException::class);
        $this->expectExceptionMessage('Unable to create a statement without a PostgreSQL connection resource');

        $driver->createStatement('SELECT 1');
    }
}
