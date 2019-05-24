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


use Akeneo\Pim\Automation\FranklinInsights\Application\Structure\Service\FindOrCreateFranklinAttributeGroupInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeGroup;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Doctrine\DBAL\Connection;
use PHPUnit\Framework\Assert;

/**
 * @author Olivier Pontier <olivier.pontier@akeneo.com>
 */
class FindOrCreateFranklinAttributeGroupIntegration extends TestCase
{
    /** @var FindOrCreateFranklinAttributeGroupInterface */
    private $findOrCreateFranklinAttributeGroupService;

    /** @var Connection */
    private $dbal;

    protected function setUp(): void
    {
        parent::setUp();
        $this->dbal = $this->get('database_connection');
        $this->findOrCreateFranklinAttributeGroupService = $this->get('akeneo.pim.automation.franklin_insights.application.structure.service.find_or_create_franklin_attribute_group');
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

        $attributeGroup = $this->findOrCreateFranklinAttributeGroupService->findOrCreate();

        Assert::assertInstanceOf(AttributeGroup::class, $attributeGroup);
        Assert::assertSame('franklin', $attributeGroup->getCode());
    }

    public function test_it_creates_new_attribute_group()
    {
        $attributeGroup = $this->findOrCreateFranklinAttributeGroupService->findOrCreate();

        $query = <<<SQL
SELECT * FROM pim_catalog_attribute_group
WHERE code = 'franklin'
SQL;
        $statement = $this->dbal->executeQuery($query);
        $result = $statement->fetch();

        Assert::assertInstanceOf(AttributeGroup::class, $attributeGroup);
        Assert::assertSame('franklin', $attributeGroup->getCode());
        Assert::assertEquals(1, $statement->rowCount());
        Assert::assertEquals('franklin', $result['code']);
    }
}