<?php

declare(strict_types=1);

namespace PhpDbTest\Pgsql\Pdo;

use PDOStatement;
use PhpDb\Adapter\Driver\Pdo\Result;
use PhpDb\Pgsql\Pdo\Connection;
use PhpDb\Pgsql\Pdo\Driver;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[Group('unit')]
#[CoversMethod(Driver::class, 'createResult')]
final class DriverTest extends TestCase
{
    #[Test]
    public function createResultCarriesTheConnectionsLastGeneratedValue(): void
    {
        $connection = $this->createStub(Connection::class);
        $connection->method('getLastGeneratedValue')->willReturn('42');

        $driver = new Driver($connection);
        $result = $driver->createResult($this->createStub(PDOStatement::class));

        static::assertInstanceOf(Result::class, $result);
        static::assertSame('42', $result->getGeneratedValue());
    }
}
