<?php

declare(strict_types=1);

namespace AkeneoTest\Pim\Enrichment\Integration\Storage\Sql\Product;

use Akeneo\Pim\Enrichment\Bundle\Storage\Sql\Product\SqlGetProductId;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use PHPUnit\Framework\Assert;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GetProductIdIntegration extends TestCase
{
    private SqlGetProductId $getProductId;
    private string $fooId;

    /** @test */
    public function it_gets_the_id_of_a_product_from_its_identifier(): void
    {
        Assert::assertSame(
            $this->fooId,
            $this->getProductId->fromIdentifier('foo')
        );
        Assert::assertNull($this->getProductId->fromIdentifier('non_existing_product'));
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->getProductId = $this->get('akeneo.pim.enrichment.product.query.get_id');
        $productFoo = $this->get('pim_catalog.builder.product')->createProduct('foo');
        $this->get('pim_catalog.saver.product')->save($productFoo);
        $this->fooId = (string) $productFoo->getId();
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }
}
