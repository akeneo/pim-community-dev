<?php


namespace AkeneoTest\Pim\Enrichment\Integration\Storage\Sql\Product;

use Akeneo\Pim\Enrichment\Bundle\Doctrine\Common\Saver\ProductSaver;
use Akeneo\Pim\Enrichment\Component\Product\Builder\ProductBuilder;
use Akeneo\Pim\Enrichment\Component\Product\Model\Product;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductRepositoryInterface;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Doctrine\ORM\EntityManager;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GetProductIdsFromFamilyIntegration extends TestCase
{
    protected function getConfiguration()
    {
        return $this->catalog->useMinimalCatalog();
    }

    /**
     * @throws \Exception
     */
    public function testQueryFetchByFamilyCode()
    {
        $familyCode = "familyCode";
        $family = $this->get('pim_catalog.factory.family')->create();
        $this->get('pim_catalog.updater.family')->update($family, ['code' => $familyCode]);
        $this->get('pim_catalog.saver.family')->save($family);


        /** @var ProductBuilder $productBuilder */
        $productBuilder = $this->get('pim_catalog.builder.product');
        $productA = $productBuilder->createProduct('productA', $familyCode);
        $productA->setFamily($family);
        $productB = $productBuilder->createProduct('productB', $familyCode);
        $productB->setFamily($family);

        /** @var ProductSaver $productSaver */
        $productSaver = $this->get('pim_catalog.saver.product');
        $productSaver->save($productA);
        $productSaver->save($productB);

        $this->assertEquals(
            ['productA', 'productB'],
            $this->get("Akeneo\Pim\Enrichment\Bundle\Storage\Sql\Product\GetProductIdsFromFamily")
                ->fetchByFamilyCode($familyCode)
        );
    }
}
