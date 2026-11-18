<?php

declare(strict_types=1);

namespace PhpDbIntegrationTest\Pgsql;

use PhpDb\Adapter\Exception\InvalidQueryException;
use PhpDb\Adapter\Exception\RuntimeException;
use PhpDb\Pgsql\Connection;
use PhpDb\Pgsql\Driver;
use PhpDbTestAsset\Pgsql\SetupTrait;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

use function restore_error_handler;
use function set_error_handler;

use const E_WARNING;

#[Group('integration')]
#[CoversMethod(Connection::class, 'beginTransaction')]
#[CoversMethod(Connection::class, 'commit')]
#[CoversMethod(Connection::class, 'connect')]
#[CoversMethod(Connection::class, 'disconnect')]
#[CoversMethod(Connection::class, 'execute')]
#[CoversMethod(Connection::class, 'getCurrentSchema')]
#[CoversMethod(Connection::class, 'getLastGeneratedValue')]
#[CoversMethod(Connection::class, 'getResource')]
#[CoversMethod(Connection::class, 'rollback')]
final class ConnectionTest extends TestCase
{
    use SetupTrait;

    #[Test]
    public function commitKeepsWhatTheTransactionWrote(): void
    {
        $connection = $this->connection();

        $connection->beginTransaction();
        $connection->execute("INSERT INTO test_connection (label) VALUES ('committed')");
        $connection->commit();

        static::assertSame(1, $this->countRows(
            $this->connection(),
            "SELECT COUNT(*) AS n FROM test_connection WHERE label = 'committed'",
        ));
    }

    #[Test]
    public function connectThrowsWhenTheServerRefusesTheConnection(): void
    {
        $connection = new Connection(['database' => 'phpdb_no_such_database'] + $this->conn);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(Connection::class . '::connect: Unable to connect to database');

        $connection->connect();
    }

    #[Test]
    public function disconnectReleasesTheConnectionResource(): void
    {
        $connection = $this->connection();
        $connection->connect();

        $connection->disconnect();

        static::assertNull($connection->getResource());
    }

    #[Test]
    public function executeThrowsTheServerErrorForInvalidSql(): void
    {
        $connection = $this->connection();
        $warnings   = [];
        $exception  = null;

        set_error_handler(static function (int $number, string $message) use (&$warnings): bool {
            $warnings[] = $message;

            return true;
        }, E_WARNING);
        try {
            $connection->execute('SELECT * FROM no_such_table');
        } catch (InvalidQueryException $caught) {
            $exception = $caught;
        } finally {
            restore_error_handler();
        }

        static::assertInstanceOf(InvalidQueryException::class, $exception);
        static::assertStringContainsString('relation "no_such_table" does not exist', $exception->getMessage());
        static::assertCount(1, $warnings);
        static::assertStringContainsString('pg_query(): Query failed', $warnings[0]);
    }

    #[Test]
    public function getCurrentSchemaReturnsTheSearchPathSchema(): void
    {
        static::assertSame('public', $this->connection()->getCurrentSchema());
    }

    #[Test]
    public function getLastGeneratedValueReturnsTheSequenceValueOfTheLastInsert(): void
    {
        $connection = $this->connection();
        $inserted   = $connection->execute("INSERT INTO test_connection (label) VALUES ('generated') RETURNING id")
            ->current();

        static::assertSame((string) $inserted['id'], $connection->getLastGeneratedValue('test_connection_id_seq'));
    }

    #[Test]
    public function rollbackDiscardsWhatTheTransactionWrote(): void
    {
        $connection = $this->connection();

        $connection->beginTransaction();
        $connection->execute("INSERT INTO test_connection (label) VALUES ('rolled back')");
        $connection->rollback();

        static::assertSame(0, $this->countRows($connection, 'SELECT COUNT(*) AS n FROM test_connection'));
    }

    protected function tearDown(): void
    {
        $this->connection()->execute('TRUNCATE test_connection RESTART IDENTITY');
    }

    private function connection(): Connection
    {
        $connection = new Connection($this->conn);
        $connection->setDriver(new Driver($connection));

        return $connection;
    }

    private function countRows(Connection $connection, string $sql): int
    {
        return (int) $connection->execute($sql)->current()['n'];
    }
}
