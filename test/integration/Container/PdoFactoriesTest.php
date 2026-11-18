<?php

declare(strict_types=1);

namespace PhpDbIntegrationTest\Pgsql\Container;

use Laminas\ServiceManager\Exception\ServiceNotCreatedException;
use PhpDb\Adapter\Exception\InvalidConnectionParametersException;
use PhpDb\Pgsql\Container\PdoConnectionInterfaceFactory;
use PhpDb\Pgsql\Container\PdoDriverInterfaceFactory;
use PhpDb\Pgsql\Pdo\Connection;
use PhpDb\Pgsql\Pdo\Driver;
use PhpDbTestAsset\Pgsql\SetupTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[Group('integration')]
#[CoversClass(PdoConnectionInterfaceFactory::class)]
#[CoversClass(PdoDriverInterfaceFactory::class)]
final class PdoFactoriesTest extends TestCase
{
    use SetupTrait;

    #[Test]
    public function connectionFactoryBuildsAConnectionFromTheGivenParameters(): void
    {
        $this->getAdapter(self::PDO_ADAPTER);

        $connection = (new PdoConnectionInterfaceFactory())(
            $this->serviceManager,
            Connection::class,
            ['connection' => $this->conn],
        );

        static::assertSame($this->conn, $connection->getConnectionParameters());
    }

    #[Test]
    public function connectionFactoryRejectsAMissingConnectionConfiguration(): void
    {
        $this->getAdapter(self::PDO_ADAPTER);

        $this->expectException(InvalidConnectionParametersException::class);
        $this->expectExceptionMessage('Connection configuration must be an array of parameters');

        (new PdoConnectionInterfaceFactory())($this->serviceManager, Connection::class);
    }

    #[Test]
    public function driverFactoryBuildsADriverOnAConnectionFromTheGivenParameters(): void
    {
        $this->getAdapter(self::PDO_ADAPTER);

        $driver = (new PdoDriverInterfaceFactory())(
            $this->serviceManager,
            Driver::class,
            ['connection' => $this->conn],
        );

        static::assertSame($this->conn, $driver->getConnection()->getConnectionParameters());
    }

    #[Test]
    public function driverFactoryRejectsAMissingConnectionConfiguration(): void
    {
        $this->getAdapter(self::PDO_ADAPTER);

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionMessage('Connection configuration must be an array of parameters');

        (new PdoDriverInterfaceFactory())($this->serviceManager, Driver::class);
    }
}
