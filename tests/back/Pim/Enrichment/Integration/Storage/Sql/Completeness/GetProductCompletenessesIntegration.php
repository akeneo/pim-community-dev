<?php

declare(strict_types=1);

namespace AkeneoTest\Pim\Enrichment\Integration\Storage\Sql\Completeness;

use Akeneo\Pim\Enrichment\Component\Product\Completeness\Model\ProductCompletenessCollection;
use Akeneo\Test\Integration\TestCase;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\ExpectationFailedException;

/**
 * @author    Mathias METAYER <mathias.metayer@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GetProductCompletenessesIntegration extends TestCase
{
    public function test_that_it_returns_completenesseses_given_a_product_id()
    {
        $this->createProduct(
            'productA',
            'familyA3',
            [
                'a_yes_no' => [
                    [
                        'scope' => null,
                        'locale' => null,
                        'data' => false,
                    ],
                ],
                'a_localized_and_scopable_text_area' => [
                    [
                        'scope' => 'ecommerce',
                        'locale' => 'en_US',
                        'data' => 'A great description',
                    ],
                    [
                        'scope' => 'tablet',
                        'locale' => 'fr_FR',
                        'data' => 'Une super description',
                    ],
                ],
            ]
        );

        $completenesses = $this->getCompletenesses($this->getProductId('productA'));
        // ecommerce + en_US
        // tablet + (en_US, de_DE, fr_FR)
        // ecommerce_china + (en_US, zh_CN)
        Assert::assertCount(6, $completenesses);
        $this->assertCompletenessContains($completenesses, 'ecommerce', 'en_US', 4, 1);
        $this->assertCompletenessContains($completenesses, 'tablet', 'en_US', 4, 2);
    }

    public function test_that_it_returns_an_empty_array_for_a_product_without_family()
    {
        $this->createProduct(
            'product_without_family',
            null,
            [
                'a_text' => [
                    [
                        'scope' => null,
                        'locale' => null,
                        'data' => 'Lorem ipsum dolor sit amet',
                    ],
                ],
                'a_localized_and_scopable_text_area' => [
                    [
                        'scope' => 'ecommerce',
                        'locale' => 'en_US',
                        'data' => 'A great description',
                    ],
                    [
                        'scope' => 'ecommerce',
                        'locale' => 'fr_FR',
                        'data' => 'Une super description',
                    ],
                ],
            ]
        );

        $completenesses = $this->getCompletenesses($this->getProductId('product_without_family'));
        Assert::assertSame(0, $completenesses->count());
    }

    protected function getConfiguration()
    {
        return $this->catalog->useTechnicalCatalog();
    }

    private function createProduct(string $identifier, ?string $familyCode, array $values): void
    {
        $product = $this->get('pim_catalog.builder.product')->createProduct($identifier, $familyCode);
        $this->get('pim_catalog.updater.product')->update($product, ['values' => $values]);
        $this->get('pim_catalog.saver.product')->save($product);
    }

    private function getProductId(string $identifier): ?int
    {
        $productId = $this->get('database_connection')->executeQuery(
            'SELECT id from pim_catalog_product where identifier = :identifier',
            ['identifier' => $identifier]
        )->fetchColumn();

        return $productId ? (int)$productId : null;
    }

    private function getCompletenesses(int $productId): ProductCompletenessCollection
    {
        return $this->get('akeneo.pim.enrichment.product.query.get_product_completenesses')
                    ->fromProductId($productId);
    }

    private function assertCompletenessContains(
        ProductCompletenessCollection $completenesses,
        string $channelCode,
        string $localeCode,
        int $requiredCount,
        int $missingCount
    ): void {
        foreach ($completenesses as $completeness) {
            if ($completeness->channelCode() === $channelCode && $completeness->localeCode() === $localeCode) {
                Assert::assertSame($requiredCount, $completeness->requiredCount());
                Assert::assertSame($missingCount, $completeness->missingCount());

                return;
            }
        }

        throw new ExpectationFailedException(
            sprintf(
                'Failed assering that completenesses contain an element with channel "%s" and locale "%s"',
                $channelCode,
                $localeCode
            )
        );
    }
}
