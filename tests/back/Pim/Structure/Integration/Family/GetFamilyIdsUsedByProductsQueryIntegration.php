<?php
declare(strict_types=1);

namespace back\Pim\Structure\Integration\Family;

use Akeneo\Pim\Structure\Component\Model\Family;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use PHPUnit\Framework\Assert;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class GetFamilyIdsUsedByProductsQueryIntegration extends TestCase
{
    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    public function test_it_gets_families_with_products(): void
    {
        $aFamilyWithAProduct = $this->createFamily(['code' => 'a_family_with_a_product']);
        $anotherFamilyWithAProduct = $this->createFamily(['code' => 'another_family_with_a_product']);
        $aFamilyWithoutAProduct = $this->createFamily(['code' => 'a_family_without_a_product']);

        $this->createProduct('a_product_with_a_family', ['family' => 'a_family_with_a_product']);
        $this->createProduct('another_product_with_a_family', ['family' => 'another_family_with_a_product']);

        $getFamilyIdsUsedByProductsQuery = $this->get(
            'Akeneo\Pim\Structure\Bundle\Query\InternalApi\Family\GetFamilyIdsUsedByProductsQuery'
        );

        $familyIdsUsedByProducts = $getFamilyIdsUsedByProductsQuery->execute();

        Assert::assertCount(2, $familyIdsUsedByProducts);
        Assert::assertEqualsCanonicalizing([$aFamilyWithAProduct->getId(), $anotherFamilyWithAProduct->getId()], $familyIdsUsedByProducts);
    }

    private function createFamily($data): Family
    {
        $family = $this->get('pim_catalog.factory.family')->create();
        $this->get('pim_catalog.updater.family')->update($family, $data);
        $this->get('pim_catalog.saver.family')->save($family);

        return $family;
    }

    private function createProduct(string $identifier, array $data): void
    {
        $product = $this->get('pim_catalog.builder.product')->createProduct($identifier);
        $this->get('pim_catalog.updater.product')->update($product, $data);
        $this->get('pim_catalog.saver.product')->save($product);
    }
}
