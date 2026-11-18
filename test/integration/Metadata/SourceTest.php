<?php

declare(strict_types=1);

namespace PhpDbIntegrationTest\Pgsql\Metadata;

use PhpDb\Adapter\Adapter;
use PhpDb\Pgsql\Metadata\Source;
use PhpDbTestAsset\Pgsql\SetupTrait;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

use function array_combine;
use function array_map;

#[Group('integration')]
#[CoversMethod(Source::class, 'loadConstraintData')]
#[CoversMethod(Source::class, 'loadTriggerData')]
final class SourceTest extends TestCase
{
    use SetupTrait;

    #[Test]
    public function getConstraintsDescribesForeignKeysWithTheirReferencedColumns(): void
    {
        $foreignKey = $this->constraintsByName('test_child')['test_child_test_id_fkey'];

        static::assertSame(
            ['FOREIGN KEY', ['test_id'], 'test', ['id'], 'CASCADE'],
            [
                $foreignKey->getType(),
                $foreignKey->getColumns(),
                $foreignKey->getReferencedTableName(),
                $foreignKey->getReferencedColumns(),
                $foreignKey->getDeleteRule(),
            ],
        );
    }

    #[Test]
    public function getConstraintsDescribesPrimaryKeyAndCheckConstraints(): void
    {
        $constraints = $this->constraintsByName('test_child');

        static::assertSame(
            ['PRIMARY KEY', ['id'], 'CHECK', '(quantity > 0)'],
            [
                $constraints['test_child_pkey']->getType(),
                $constraints['test_child_pkey']->getColumns(),
                $constraints['test_child_quantity_check']->getType(),
                $constraints['test_child_quantity_check']->getCheckClause(),
            ],
        );
    }

    #[Test]
    public function getTriggersListsTriggersByName(): void
    {
        $trigger = $this->source()->getTrigger('test_child_before_insert');

        static::assertSame(
            ['test_child_before_insert', 'INSERT', 'test_child', 'BEFORE'],
            [
                $trigger->getName(),
                $trigger->getEventManipulation(),
                $trigger->getEventObjectTable(),
                $trigger->getActionTiming(),
            ],
        );
    }

    /**
     * @return array<string, \PhpDb\Metadata\Object\ConstraintObject>
     */
    private function constraintsByName(string $table): array
    {
        $constraints = $this->source()->getConstraints($table);

        return array_combine(array_map(static fn($c) => $c->getName(), $constraints), $constraints);
    }

    private function source(): Source
    {
        /** @var Adapter $adapter */
        $adapter = $this->getAdapter();

        return new Source($adapter);
    }
}
