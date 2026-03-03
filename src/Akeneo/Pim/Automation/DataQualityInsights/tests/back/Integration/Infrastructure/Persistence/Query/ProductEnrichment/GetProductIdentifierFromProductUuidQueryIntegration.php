<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Automation\DataQualityInsights\Integration\Infrastructure\Persistence\Query\ProductEnrichment;

use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductUuidFactory;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\ProductEnrichment\GetProductIdentifierFromProductUuidQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductIdentifier;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductUuid;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Query\ProductEnrichment\GetProductIdentifierFromProductUuidQuery;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;

/**
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GetProductIdentifierFromProductUuidQueryIntegration extends TestCase
{
    /** @var GetProductIdentifierFromProductUuidQueryInterface */
    private $query;

    protected function setUp(): void
    {
        parent::setUp();

        $this->query = $this->get(GetProductIdentifierFromProductUuidQuery::class);
    }

    /**
     * @test
     */
    public function it_gets_a_product_identifier_from_its_id()
    {
        $productId = $this->createProduct('ziggy_mug');
        $productIdentifier = $this->query->execute($productId);

        $this->assertEquals(new ProductIdentifier('ziggy_mug'), $productIdentifier);
    }

    /**
     * @test
     */
    public function it_throws_an_exception_if_the_product_does_not_exist()
    {
        $this->expectExceptionMessage('No identifier found for product uuid df470d52-7723-4890-85a0-e79be625e2ed');
        $this->query->execute(ProductUuid::fromString(('df470d52-7723-4890-85a0-e79be625e2ed')));
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    private function createProduct(string $identifier): ProductUuid
    {
        $product = $this->get('akeneo_integration_tests.catalog.product.builder')
            ->withIdentifier($identifier)
            ->build();

        $this->get('pim_catalog.saver.product')->save($product);
        return $this->get(ProductUuidFactory::class)->create((string)$product->getUuid());
    }
}
