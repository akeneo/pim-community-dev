<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


namespace Akeneo\Pim\Automation\FranklinInsights\tests\back\Integration\Structure\Service;


use Akeneo\Pim\Automation\FranklinInsights\Application\Structure\Service\EnsureFranklinAttributeGroupExistsInterface;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\FranklinAttributeGroup;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Doctrine\DBAL\Connection;
use PHPUnit\Framework\Assert;

/**
 * @author Olivier Pontier <olivier.pontier@akeneo.com>
 */
class EnsureFranklinAttributeGroupExistsIntegration extends TestCase
{
    /** @var EnsureFranklinAttributeGroupExistsInterface */
    private $ensureFranklinAttributeGroupExists;

    /** @var Connection */
    private $dbal;

    protected function setUp(): void
    {
        parent::setUp();
        $this->dbal = $this->get('database_connection');
        $this->ensureFranklinAttributeGroupExists = $this->get('akeneo.pim.automation.franklin_insights.application.structure.service.ensure_franklin_attribute_group_exists');
    }

    /**
     * @return Configuration
     */
    protected function getConfiguration()
    {
        return $this->catalog->useMinimalCatalog();
    }

    public function test_it_finds_existing_attribute_group()
    {
        $query = <<<SQL
INSERT INTO pim_catalog_attribute_group (code, sort_order, created, updated) 
VALUES ('franklin', 20, NOW(), NOW())
SQL;
        $this->dbal->executeQuery($query);

        $this->ensureFranklinAttributeGroupExists->ensureExistence();

        $stmt = $this->executeGetFranklinAttributeGroupQuery();
        Assert::assertEquals(1, $stmt->rowCount());
    }

    public function test_it_creates_new_attribute_group()
    {
        $stmt = $this->executeGetFranklinAttributeGroupQuery();
        Assert::assertEquals(0, $stmt->rowCount());

        $this->ensureFranklinAttributeGroupExists->ensureExistence();

        $stmt = $this->executeGetFranklinAttributeGroupQuery();
        Assert::assertEquals(1, $stmt->rowCount());
    }

    private function executeGetFranklinAttributeGroupQuery()
    {
        $query = <<<SQL
SELECT * FROM pim_catalog_attribute_group
WHERE code = 'franklin'
SQL;
        return $this->dbal->executeQuery($query);
    }
}
