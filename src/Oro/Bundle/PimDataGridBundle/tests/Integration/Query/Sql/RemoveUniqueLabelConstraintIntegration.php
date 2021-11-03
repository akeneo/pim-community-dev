<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Oro\Bundle\PimDataGridBundle\tests\Integration\Query\Sql;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Doctrine\DBAL\FetchMode;
use Oro\Bundle\PimDataGridBundle\Query\Sql\RemoveUniqueLabelConstraint;
use PHPUnit\Framework\Assert;

final class RemoveUniqueLabelConstraintIntegration extends TestCase
{
    /** @test */
    public function it_removes_the_constraint_if_exists()
    {
        /** @var RemoveUniqueLabelConstraint $remover */
        $remover = $this->get(RemoveUniqueLabelConstraint::class);

        if (!$this->constraintExists()) {
            $this->createUniqueIndex();
        }

        Assert::assertTrue($this->constraintExists());
        $remover->removeIfExists();
        Assert::assertFalse($this->constraintExists());

        $remover->removeIfExists();
        Assert::assertFalse($this->constraintExists());
    }

    private function constraintExists(): bool
    {
        $databaseNameSql = 'SELECT database()';
        $databaseName = $this->get('database_connection')->executeQuery($databaseNameSql)->fetch(FetchMode::COLUMN);
        Assert::assertIsString($databaseName);

        $findConstraintNameSql = <<< SQL
        SELECT DISTINCT CONSTRAINT_NAME
        FROM information_schema.TABLE_CONSTRAINTS
        WHERE table_name = 'pim_datagrid_view' AND constraint_type = 'UNIQUE' AND TABLE_SCHEMA = :database_name;
        SQL;

        $uniqueConstraintName = $this->get('database_connection')->executeQuery($findConstraintNameSql, [
            'database_name' => $databaseName,
        ])->fetch(FetchMode::COLUMN);

        return is_string($uniqueConstraintName);
    }

    private function createUniqueIndex()
    {
        $this->get('database_connection')->executeQuery(
            'CREATE UNIQUE INDEX pim_datagrid_view_label_unique ON pim_datagrid_view (label)'
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }
}
