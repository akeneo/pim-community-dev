<?php

declare(strict_types=1);

namespace AkeneoTest\Pim\Enrichment\Integration\Storage\Sql\ProductModel;

use Akeneo\Pim\Enrichment\Bundle\Storage\Sql\ProductModel\SqlFindProductModelId;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use PHPUnit\Framework\Assert;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FindProductModelIdIntegration extends TestCase
{
    private SqlFindProductModelId $getProductModelId;
    private string $fooId;

    /** @test */
    public function it_gets_the_id_of_a_product_model_from_its_code(): void
    {
        Assert::assertSame(
            $this->fooId,
            $this->getProductModelId->fromIdentifier('foo')
        );
        Assert::assertNull($this->getProductModelId->fromIdentifier('non_existing_product'));
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->getProductModelId = $this->get('akeneo.pim.enrichment.product_model.query.find_id');
        $productModelFoo = $this->get('pim_catalog.factory.product_model')->create();
        $this->get('pim_catalog.updater.product_model')->update($productModelFoo, [
            'code' => 'foo',
            'family_variant' => 'familyVariantA1',
        ]);
        $this->get('pim_catalog.saver.product_model')->save($productModelFoo);
        $this->fooId = (string) $productModelFoo->getId();
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useTechnicalCatalog();
    }
}
