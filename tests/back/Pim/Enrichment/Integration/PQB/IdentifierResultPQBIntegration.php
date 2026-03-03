<?php

declare(strict_types=1);

namespace AkeneoTest\Pim\Enrichment\Integration\PQB;

use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\IdentifierResult;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators;
use PHPUnit\Framework\Assert;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class IdentifierResultPQBIntegration extends AbstractProductQueryBuilderTestCase
{
    /** @test */
    public function it_returns_all_the_product_identifiers(): void
    {
        $products = [];
        for ($i = 1; $i <= 1100; $i++) {
            $products[] = $this->get('pim_catalog.builder.product')->createProduct('identifier' . $i, 'familyA');
        }
        $this->get('pim_catalog.saver.product')->saveAll($products);
        $this->esProductClient->refreshIndex();

        $pqb = $this->get('pim_catalog.query.product_identifier_query_builder_factory')->create();
        $pqb->addFilter('family', Operators::IN_LIST, ['familyA']);

        $count = 0;
        foreach ($pqb->execute() as $identifierResult) {
            Assert::assertInstanceOf(IdentifierResult::class, $identifierResult);
            $this->assertSame(ProductInterface::class, $identifierResult->getType());
            Assert::assertStringContainsString('identifier', $identifierResult->getIdentifier());
            $count++;
        }

        Assert::assertSame(1100, $count);
    }
}
