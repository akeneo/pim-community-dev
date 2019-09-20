<?php

declare(strict_types=1);

namespace AkeneoTest\Pim\Enrichment\Integration\Completeness\Import;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Akeneo\Test\IntegrationTestsBundle\Launcher\JobLauncher;
use PHPUnit\Framework\Assert;

class ComputeCompletenessOnProductImportIntegration extends TestCase
{
    /**
     * @group critical
     */
    public function test_that_completeness_is_computed_when_products_are_imported(): void
    {
        $csv = <<<CSV
sku;family;groups;categories;name-en_US;description-en_US-tablet;price;size;color
SKU-001;boots;similar_boots;winter_boots;Donec;dictum magna. Ut tincidunt orci quis lectus. Nullam suscipit, est;"100 EUR, 90 USD";40;
SKU-002;sneakers;;winter_boots;Donex;Pellentesque habitant morbi tristique senectus et netus et malesuada fames;"100 EUR, 90 USD";37;red      
CSV;
        $jobLauncher = new JobLauncher(static::$kernel);
        $jobLauncher->launchAuthenticatedSubProcessImport('csv_footwear_product_import', $csv, 'Julia');

        $this->assertCompleteness('SKU-001', [
            [
                'channel' => 'mobile',
                'locale' => 'en_US',
                'missing' => 1,
                'ratio' => 80,
            ],
            [
                'channel' => 'mobile',
                'locale' => 'fr_FR',
                'missing' => 2,
                'ratio' => 60,
            ],
            [
                'channel' => 'tablet',
                'locale' => 'en_US',
                'missing' => 4,
                'ratio' => 55,
            ],
        ]);

        $this->assertCompleteness(
            'SKU-002',
            [
                [
                    'channel' => 'mobile',
                    'locale' => 'en_US',
                    'missing' => 0,
                    'ratio' => 100,
                ],
                [
                    'channel' => 'mobile',
                    'locale' => 'fr_FR',
                    'missing' => 1,
                    'ratio' => 80,
                ],
                [
                    'channel' => 'tablet',
                    'locale' => 'en_US',
                    'missing' => 3,
                    'ratio' => 66,
                ],
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useFunctionalCatalog('footwear');
    }

    private function assertCompleteness(string $productIdentifier, array $expectedCompletenesses): void
    {
        $productId = $this->get('database_connection')->executeQuery(
            'select id from pim_catalog_product where identifier = :identifier',
            [
                'identifier' => $productIdentifier,
            ]
        )->fetchColumn();

        $actualCompletenesses = $this->get('akeneo.pim.enrichment.product.query.get_product_completenesses')
            ->fromProductId((int)$productId);

        Assert::assertCount(count($expectedCompletenesses), $actualCompletenesses);
        foreach ($expectedCompletenesses as $expectedCompleteness) {
            $actualCompleteness = $actualCompletenesses->getCompletenessForChannelAndLocale(
                $expectedCompleteness['channel'],
                $expectedCompleteness['locale']
            );
            Assert::assertNotNull($actualCompleteness);
            Assert::assertSame($expectedCompleteness['missing'], $actualCompleteness->missingCount());
            Assert::assertSame($expectedCompleteness['ratio'], $actualCompleteness->ratio());
        }
    }
}
