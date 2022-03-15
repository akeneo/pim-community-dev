<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2022 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Akeneo\Test\Pim\TableAttribute\Integration;

use Akeneo\Pim\Enrichment\Component\Product\Completeness\Model\ProductCompleteness;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Akeneo\Test\IntegrationTestsBundle\Launcher\JobLauncher;
use Akeneo\Test\Pim\TableAttribute\Helper\EntityBuilderTrait;
use Doctrine\DBAL\Connection;
use PHPUnit\Framework\Assert;

class ComputeCompletenessOfTableAttributeProductsIntegration extends TestCase
{
    use EntityBuilderTrait;
    private JobLauncher $jobLauncher;
    private Connection $connection;

    /** @test */
    public function computeCompletenessOfTableAttributeProductsWhenCompletenessIsUpdated(): void
    {
        $this->assertCompleteness('sunglasses', 'ecommerce', 'en_US', 100);
        $attribute = $this->get('pim_catalog.repository.attribute')->findOneByIdentifier('nutrition');
        $this->get('pim_catalog.updater.attribute')->update(
            $attribute,
            $this->getTableConfiguration(true)
        );
        $violations = $this->get('validator')->validate($attribute);
        Assert::assertCount(0, $violations, \sprintf('The attribute is not valid: %s', $violations));
        $this->get('pim_catalog.saver.attribute')->save($attribute);

        $this->jobLauncher->launchConsumerUntilQueueIsEmpty();
        $this->assertJobSuccessful();
        $this->assertCompleteness('sunglasses', 'ecommerce', 'en_US', 50);
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->jobLauncher = $this->get('akeneo_integration_tests.launcher.job_launcher');
        $this->connection = $this->get('database_connection');

        $this->createAttribute($this->getTableConfiguration(false));

        $this->createFamily([
            'code' => 'food',
            'attributes' => ['sku', 'nutrition'],
            'attribute_requirements' => [
                'ecommerce' => ['sku', 'nutrition'],
            ]
        ]);

        $this->createProduct('sunglasses', [
            'values' => [
                'nutrition' => [
                    [
                        'locale' => null,
                        'scope' => null,
                        'data' => [
                            ['ingredient' => 'salt', 'is_allergenic' => false],
                            ['ingredient' => 'egg', 'is_allergenic' => false, 'quantity' => 1],
                        ],
                    ],
                ],
            ],
            'family' => 'food'
        ]);
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    private function getTableConfiguration(bool $quantiyCompleteness): array
    {
        $options = \array_map(
            fn (int $num): array => ['code' => \sprintf('option_%d', $num)],
            \range(1, 19997)
        );
        return [
            'code' => 'nutrition',
            'type' => AttributeTypes::TABLE,
            'group' => 'other',
            'localizable' => false,
            'scopable' => false,
            'table_configuration' => [
                [
                    'code' => 'ingredient',
                    'data_type' => 'select',
                    'labels' => [
                        'en_US' => 'Ingredients',
                    ],
                    'options' => \array_merge(
                        [
                            ['code' => 'salt'],
                            ['code' => 'egg'],
                            ['code' => 'butter'],
                        ],
                        $options
                    ),
                    'is_required_for_completeness' => true,
                ],
                [
                    'code' => 'quantity',
                    'data_type' => 'number',
                    'labels' => [
                        'en_US' => 'Quantity',
                    ],
                    'is_required_for_completeness' => $quantiyCompleteness,
                ],
                [
                    'code' => 'is_allergenic',
                    'data_type' => 'boolean',
                    'labels' => [
                        'en_US' => 'Is allergenic',
                    ],
                    'is_required_for_completeness' => true,
                ],
            ],
        ];
    }

    private function assertJobSuccessful()
    {
        $res = $this->connection->executeQuery(
            <<<SQL
            SELECT execution.status = 1 AS success
            FROM akeneo_batch_job_execution execution
            INNER JOIN akeneo_batch_job_instance instance ON execution.job_instance_id = instance.id
            WHERE instance.code = 'compute_completeness_following_table_update'
            ORDER BY execution.id DESC LIMIT 1
        SQL
        )->fetchOne();

        Assert::assertTrue((bool)$res, 'The compute completeness job was not successful');
    }

    private function getCompletenesses(
        ProductInterface $product,
        string $channelCode,
        string $localeCode
    ): ?ProductCompleteness {
        $completenesses = $this->get('akeneo.pim.enrichment.product.query.get_product_completenesses')
            ->fromProductId($product->getId());

        foreach ($completenesses as $completeness) {
            if ($channelCode === $completeness->channelCode() && $localeCode === $completeness->localeCode()) {
                return $completeness;
            }
        }

        return null;
    }

    private function assertCompleteness(string $productIdentifier, string $channelCode, string $localeCode, int $ratio): void
    {
        $this->get('doctrine.orm.default_entity_manager')->clear();

        $product = $this->get('pim_catalog.repository.product')->findOneByIdentifier($productIdentifier);
        $completeness = $this->getCompletenesses($product, $channelCode, $localeCode);

        $this->assertNotNull($completeness);
        $this->assertEquals($ratio, $completeness->ratio());
    }
}
