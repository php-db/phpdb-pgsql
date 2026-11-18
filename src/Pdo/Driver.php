<?php

declare(strict_types=1);

namespace PhpDb\Pgsql\Pdo;

use Override;
use PDO;
use PDOStatement;
use PhpDb\Adapter\Driver\Pdo\AbstractPdo;
use PhpDb\Adapter\Driver\Pdo\Result;
use PhpDb\Adapter\Driver\Pdo\Statement;
use PhpDb\Adapter\Driver\PdoConnectionInterface;
use PhpDb\Adapter\Driver\PdoDriverAwareInterface;
use PhpDb\Adapter\Driver\ResultInterface;
use PhpDb\Adapter\Driver\StatementInterface;

class Driver extends AbstractPdo
{
    public function __construct(
        (PdoConnectionInterface&PdoDriverAwareInterface)|PDO $connection,
        StatementInterface&PdoDriverAwareInterface $statementPrototype = new Statement(),
        ResultInterface $resultPrototype = new Result(),
    ) {
        $this->connection         = $connection;
        $this->statementPrototype = $statementPrototype;
        $this->resultPrototype    = $resultPrototype;

        if (! $this->connection instanceof PDO) {
            $this->connection->setDriver($this);
        }

        $this->statementPrototype->setDriver($this);
    }

    /**
     * @param PDOStatement|resource $resource
     */
    #[Override]
    public function createResult($resource): ResultInterface
    {
        /** @var Result $result */
        $result = clone $this->resultPrototype;
        $result->initialize($resource, $this->getLastGeneratedValue());

        return $result;
    }
}
