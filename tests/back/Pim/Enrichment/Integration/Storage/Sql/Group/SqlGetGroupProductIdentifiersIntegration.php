<?php

declare(strict_types=1);

namespace AkeneoTest\Pim\Enrichment\Integration\Storage\Sql\Group;

use Akeneo\Pim\Enrichment\Component\Product\Model\GroupInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\GetGroupProductIdentifiers;
use Akeneo\Test\Integration\TestCase;
use Webmozart\Assert\Assert;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SqlGetGroupProductIdentifiersIntegration extends TestCase
{
    protected function getConfiguration()
    {
        return $this->catalog->useMinimalCatalog();
    }

    public function test_it_returns_the_identifiers_of_the_products_of_a_given_group()
    {
        $groupA = $this->givenAGroup('groupA');
        $groupB = $this->givenAGroup('groupB');

        $this->givenProductsWithGroup(['product_1', 'product_2'], 'groupA');
        $this->givenAProductWithoutGroup();

        $this->assertEmpty($this->getQuery()->byGroupId($groupB->getId()));

        $productIdentifiers = $this->getQuery()->byGroupId($groupA->getId());
        $this->assertEqualsCanonicalizing(['product_1', 'product_2'], $productIdentifiers);
    }

    private function getQuery(): GetGroupProductIdentifiers
    {
        return $this->get('akeneo.pim.enrichment.group.query.get_group_product_identifiers');
    }

    private function givenAGroup(string $groupCode): GroupInterface
    {
        $group = $this->get('pim_catalog.factory.group')->createGroup('RELATED');
        $this->get('pim_catalog.updater.group')->update($group, [
            'code' => $groupCode
        ]);

        $errors = $this->get('validator')->validate($group);
        Assert::count($errors, 0);

        $this->get('pim_catalog.saver.group')->save($group);

        return $group;
    }

    private function givenProductsWithGroup(array $productIdentifiers, string $groupCode): void
    {
        foreach ($productIdentifiers as $productIdentifier) {
            $this->createProduct($productIdentifier, ['groups' => [$groupCode]]);
        }
    }

    private function givenAProductWithoutGroup(): void
    {
        $this->createProduct('whatever');
    }

    private function createProduct(string $identifier, array $data = []): void
    {
        $product = $this->get('pim_catalog.builder.product')->createProduct($identifier);
        $this->get('pim_catalog.updater.product')->update($product, $data);

        $errors = $this->get('pim_catalog.validator.product')->validate($product);
        Assert::count($errors, 0);

        $this->get('pim_catalog.saver.product')->save($product);
    }
}
