<?php
declare(strict_types=1);

namespace AkeneoTest\Pim\Structure\Integration\Doctrine\ORM\Repository\ExternalApi;

use Akeneo\Pim\Structure\Bundle\Doctrine\ORM\Repository\ExternalApi\AttributeGroupRepository;
use Akeneo\Pim\Structure\Component\Model\AttributeGroup;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Doctrine\DBAL\Connection;
use PHPUnit\Framework\Assert;

class AttributeGroupRepositoryApiResourceIntegration extends TestCase
{
    public function test_to_get_the_identifier_properties(): void
    {
        $properties = $this->getRepository()->getIdentifierProperties();
        Assert::assertEquals(['code'], $properties);
    }

    public function test_to_find_a_attribute_group_by_code(): void
    {
        $this->initFixtures();
        $attributeGroup = $this->getRepository()->findOneByIdentifier('technical');

        Assert::assertInstanceOf(AttributeGroup::class, $attributeGroup);
        Assert::assertEquals('technical', $attributeGroup->getCode());
    }

    public function test_to_get_attribute_groups(): void
    {
        $this->initFixtures();
        $attributeGroups = $this->getRepository()->searchAfterOffset([], [], 10, 0);
        Assert::assertCount(4, $attributeGroups);
    }

    public function test_to_count_attribute_groups(): void
    {
        $this->initFixtures();
        $count = $this->getRepository()->count([]);
        Assert::assertEquals(4, $count);
    }

    public function test_to_count_attribute_groups_with_search(): void
    {
        $this->initFixtures();
        $count = $this->getRepository()->count(['code' => [['operator' => 'IN', 'value' => ['marketing', 'other']]]]);
        Assert::assertEquals(2, $count);
    }

    public function test_to_get_attribute_groups_with_limit(): void
    {
        $this->initFixtures();
        $attributeGroups = $this->getRepository()->searchAfterOffset([], ['code' => 'ASC'], 2, 0);
        Assert::assertCount(2, $attributeGroups);
        Assert::assertEquals('delivery', $attributeGroups[0]->getCode());
        Assert::assertEquals('marketing', $attributeGroups[1]->getCode());
    }

    public function test_to_search_attribute_groups_after_the_offset(): void
    {
        $this->initFixtures();
        $attributeGroups = $this->getRepository()->searchAfterOffset([], ['code' => 'ASC'], 2, 2);
        Assert::assertCount(2, $attributeGroups);
        Assert::assertEquals('other', $attributeGroups[0]->getCode());
        Assert::assertEquals('technical', $attributeGroups[1]->getCode());
    }

    // TODO : Fix
    /*
    public function test_to_search_ordered_attribute_groups(): void
    {
        $this->initFixtures();
        $this->updateDatetime();

        $attributeGroupsCodeDesc = $this->getRepository()->searchAfterOffset([], ['code' => 'DESC'], 4, 0);
        Assert::assertCount(4, $attributeGroupsCodeDesc);
        Assert::assertEquals('technical', $attributeGroupsCodeDesc[0]->getCode());
        Assert::assertEquals('other', $attributeGroupsCodeDesc[1]->getCode());
        Assert::assertEquals('marketing', $attributeGroupsCodeDesc[2]->getCode());
        Assert::assertEquals('delivery', $attributeGroupsCodeDesc[3]->getCode());

        $attributeGroupsCodeAsc = $this->getRepository()->searchAfterOffset([], ['updated' => 'DESC', 'code' => 'ASC'], 4, 0);
        Assert::assertCount(4, $attributeGroupsCodeAsc);
        Assert::assertEquals('delivery', $attributeGroupsCodeAsc[0]->getCode());
        Assert::assertEquals('technical', $attributeGroupsCodeAsc[1]->getCode());
        Assert::assertEquals('other', $attributeGroupsCodeAsc[2]->getCode());
        Assert::assertEquals('marketing', $attributeGroupsCodeAsc[3]->getCode());
    }
    */

    public function test_to_search_attribute_groups_by_codes(): void
    {
        $this->initFixtures();

        $attributeGroups = $this->getRepository()->searchAfterOffset(
            ['code' => [['operator' => 'IN', 'value' => ['technical', 'delivery']]]],
            ['code' => 'ASC'],
            5,
            0
        );
        Assert::assertCount(2, $attributeGroups);
        Assert::assertEquals('delivery', $attributeGroups[0]->getCode());
        Assert::assertEquals('technical', $attributeGroups[1]->getCode());
    }

    public function test_to_search_attribute_groups_by_updated_date(): void
    {
        $this->initFixtures();
        $this->updateDatetime();

        $since2019 = new \DateTime('2019-01-01 00:00:00', new \DateTimeZone('UTC'));
        $since2019Groups = $this->getRepository()->searchAfterOffset(
            ['updated' => [['operator' => '>', 'value' => $since2019->format(DATE_ATOM)]]],
            ['code' => 'ASC'],
            5,
            0
        );
        Assert::assertCount(3, $since2019Groups);
        Assert::assertEquals('delivery', $since2019Groups[0]->getCode());
        Assert::assertEquals('other', $since2019Groups[1]->getCode());
        Assert::assertEquals('technical', $since2019Groups[2]->getCode());

        $since2020 = new \DateTime('2020-01-01 00:00:00', new \DateTimeZone('UTC'));
        $since2020Groups = $this->getRepository()->searchAfterOffset(
            ['updated' => [['operator' => '>', 'value' => $since2020->format(DATE_ATOM)]]],
            ['code' => 'ASC'],
            5,
            0
        );
        Assert::assertCount(2, $since2020Groups);
        Assert::assertEquals('other', $since2019Groups[1]->getCode());
        Assert::assertEquals('technical', $since2019Groups[2]->getCode());
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    private function getRepository(): AttributeGroupRepository
    {
        return $this->get('pim_api.repository.attribute_group');
    }

    private function updateDatetime(): void
    {
        /** @var Connection $connection */
        $connection = $this->get('database_connection');
        $query2018 = <<<SQL
UPDATE pim_catalog_attribute_group
SET updated= '2018-01-01 16:27:00'
WHERE code = 'marketing'
SQL;
        $connection->executeUpdate($query2018);

        $query2019 = <<<SQL
UPDATE pim_catalog_attribute_group
SET updated= '2019-01-01 16:27:00'
WHERE code = 'other'
SQL;
        $connection->executeUpdate($query2019);

    }

    private function initFixtures(): void
    {
        $this->createAttributeGroup(['code' => 'delivery']);
        $this->createAttributeGroup(['code' => 'marketing']);
        $this->createAttributeGroup(['code' => 'technical']);
    }

    private function createAttributeGroup($data): void
    {
        $attributeGroup = $this->get('pim_catalog.factory.attribute_group')->create();
        $this->get('pim_catalog.updater.attribute_group')->update($attributeGroup, $data);

        $this->get('pim_catalog.saver.attribute_group')->save($attributeGroup);
    }
}
