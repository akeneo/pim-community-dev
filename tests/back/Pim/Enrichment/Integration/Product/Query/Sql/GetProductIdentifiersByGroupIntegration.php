<?php

declare(strict_types=1);

namespace AkeneoTest\Pim\Enrichment\Integration\Product\Query\Sql;

use Akeneo\Pim\Enrichment\Bundle\Product\Query\Sql\GetProductIdentifiersByGroup;
use Akeneo\Test\Integration\TestCase;
use AkeneoTest\Pim\Enrichment\Integration\Fixture\EntityBuilder;
use Webmozart\Assert\Assert;

/**
 * @author    JM Leroux <jean-marie.leroux@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GetProductIdentifiersByGroupIntegration extends TestCase
{
    /** @var int */
    private $groupIds = [];

    public function setUp(): void
    {
        parent::setUp();

        $entityBuilder = new EntityBuilder($this->testKernel->getContainer());
        $this->givenFamily('aFamily');
        $this->givenGroups(['groupA', 'groupB']);

        $entityBuilder->createProduct('productA', 'aFamily', ['groups' => ['groupA']]);
        $entityBuilder->createProduct('productB', 'aFamily', ['groups' => ['groupB']]);
        $entityBuilder->createProduct('productC', 'aFamily', ['groups' => ['groupA', 'groupB']]);
        $entityBuilder->createProduct('productD', 'aFamily', []);
    }

    public function testFetchProductIdentifiers()
    {
        $this->assertEqualsCanonicalizing(
            [
                'productA',
                'productC',
            ],
            $this->getQuery()->fetchByGroupId($this->groupIds['groupA'])
        );
        $this->assertEqualsCanonicalizing(
            [
                'productB',
                'productC',
            ],
            $this->getQuery()->fetchByGroupId($this->groupIds['groupB'])
        );
    }

    public function testUnknownGroup()
    {
        $this->assertEqualsCanonicalizing(
            [],
            $this->getQuery()->fetchByGroupId(666)
        );
    }

    private function getQuery(): GetProductIdentifiersByGroup
    {
        return $this->testKernel->getContainer()->get('akeneo.pim.enrichment.product.query.product_identifiers_by_group');
    }

    private function givenGroups(array $groups): void
    {
        foreach ($groups as $groupCode) {
            $group = $this->get('pim_catalog.factory.group')->create();
            $this->get('pim_catalog.updater.group')->update($group, [
                'code' => $groupCode,
                'type' => 'RELATED',
            ]);
            $this->get('pim_catalog.saver.group')->save($group);
            $this->groupIds[$groupCode] = $group->getId();
        }
    }

    private function givenFamily(string $familyCode): void
    {
        $family = $this->get('pim_catalog.factory.family')->create();
        $this->get('pim_catalog.updater.family')->update($family, [
            'code' => $familyCode,
            'attributes' => ['sku'],
            'attribute_requirements' => ['ecommerce' => ['sku']],
        ]);
        $this->get('pim_catalog.saver.family')->save($family);
    }

    protected function getConfiguration()
    {
        return $this->catalog->useMinimalCatalog();
    }
}
