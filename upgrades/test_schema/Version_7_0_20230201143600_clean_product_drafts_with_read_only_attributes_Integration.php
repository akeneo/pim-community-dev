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

namespace Pim\Upgrade\Schema\Tests;

use Akeneo\Pim\Enrichment\Product\API\Command\UpsertProductCommand;
use Akeneo\Pim\Enrichment\Product\API\ValueObject\ProductIdentifier;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Test\Integration\TestCase;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Types;
use PHPUnit\Framework\Assert;

class Version_7_0_20230201143600_clean_product_drafts_with_read_only_attributes_Integration extends TestCase
{
    use ExecuteMigrationTrait;

    private const MIGRATION_LABEL = '_7_0_20230201143600_clean_product_drafts_with_read_only_attributes';
    private Connection $connection;

    protected function setUp(): void
    {
        parent::setUp();
        $this->connection = $this->get('database_connection');
    }

    protected function getConfiguration()
    {
        return $this->catalog->useTechnicalCatalog();
    }

    public function test_it_update_nothing_when_no_read_only(): void
    {
        $this->createProduct('my_product', []);
        $this->createAttribute('my_attribute', []);
        $connection = $this->createMock('Doctrine\DBAL\Connection');

        $this->reExecuteMigration(self::MIGRATION_LABEL);

        $connection->expects($this->never())->method('transactional');
    }

    public function test_it_update_nothing_when_no_read_only_attribute_in_draft()
    {
        $this->createProduct('my_product', []);
        $this->createAttribute('my_attribute_read_only', ['is_read_only' => true]);
        $this->createAttribute('my_attribute', []);
        $productUuid = $this
            ->get('Akeneo\Pim\Enrichment\Product\Infrastructure\Query\SqlGetProductUuids')
            ->fromIdentifier('my_product');
        $changes = [
            "values" => [
                "my_attribute" => [[
                    "data" => "myAttributeData",
                    "scope" => null,
                    "locale" => null
                ]],
                "sku" => [[
                    "data" => "skuData",
                    "scope" => null,
                    "locale" => null
                ]]
            ],
            "review_statuses" => [
                "my_attribute" => [[
                    "scope" => null,
                    "locale" => null,
                    "status" => "to_review"
                ]],
                "sku" => [[
                    "scope" => null,
                    "locale" => null,
                    "status" => "to_review"
                ]]
            ]
        ];
        $rawValues = [
            "my_attribute" => [
                "<all_channels>" => [
                    "<all_locales>" => "myAttributeData"
                ]
            ],
            "sku" => [
                "<all_channels>" => [
                    "<all_locales>" => "skuNewData"
                ]
            ]
        ];
        $this->createProductDraft($productUuid->toString(), $changes, $rawValues);

        $this->reExecuteMigration(self::MIGRATION_LABEL);

        $draft = $this->getProductDraft();
        $expectedChanges = [
            "values" => [
                "my_attribute" => [[
                    "data" => "myAttributeData",
                    "scope" => null,
                    "locale" => null
                ]],
                "sku" => [[
                    "data" => "skuData",
                    "scope" => null,
                    "locale" => null
                ]]
            ],
            "review_statuses" => [
                "my_attribute" => [[
                    "scope" => null,
                    "locale" => null,
                    "status" => "to_review"
                ]],
                "sku" => [[
                    "scope" => null,
                    "locale" => null,
                    "status" => "to_review"
                ]]
            ]
        ];
        $expectedRawValues = [
            "my_attribute" => [
                "<all_channels>" => [
                    "<all_locales>" => "myAttributeData"
                ]
            ],
            "sku" => [
                "<all_channels>" => [
                    "<all_locales>" => "skuNewData"
                ]
            ]
        ];
        Assert::assertEquals($expectedChanges, json_decode($draft['changes'], true));
        Assert::assertEquals($expectedRawValues, json_decode($draft['raw_values'], true));
    }

    public function test_it_clean_draft_with_read_only_attribute()
    {
        $this->createProduct('my_product', []);
        $this->createAttribute('my_attribute_read_only', ['is_read_only' => true]);
        $productUuid = $this
            ->get('Akeneo\Pim\Enrichment\Product\Infrastructure\Query\SqlGetProductUuids')
            ->fromIdentifier('my_product');
        $changes = [
            "values" => [
                "my_attribute_read_only" => [[
                    "data" => "readOnlyData",
                    "scope" => null,
                    "locale" => null
                ]],
                "sku" => [[
                    "data" => "skuData",
                    "scope" => null,
                    "locale" => null
                ]]
            ],
            "review_statuses" => [
                "my_attribute_read_only" => [[
                    "scope" => null,
                    "locale" => null,
                    "status" => "to_review"
                ]],
                "sku" => [[
                    "scope" => null,
                    "locale" => null,
                    "status" => "to_review"
                ]]
            ]
        ];
        $rawValues = [
            "my_attribute_read_only" => [
                "<all_channels>" => [
                    "<all_locales>" => "readOnlyData"
                ]
            ],
            "sku" => [
                "<all_channels>" => [
                    "<all_locales>" => "skuNewData"
                ]
            ]
        ];
        $this->createProductDraft($productUuid->toString(), $changes, $rawValues);

        $this->reExecuteMigration(self::MIGRATION_LABEL);

        $draft = $this->getProductDraft();
        $expectedChanges = [
            "values" => [
                "sku" => [[
                    "data" => "skuData",
                    "scope" => null,
                    "locale" => null
                ]]
            ],
            "review_statuses" => [
                "sku" => [[
                    "scope" => null,
                    "locale" => null,
                    "status" => "to_review"
                ]]
            ]
        ];
        $expectedRawValues = [
            "sku" => [
                "<all_channels>" => [
                    "<all_locales>" => "skuNewData"
                ]
            ]
        ];
        Assert::assertEquals($expectedChanges, json_decode($draft['changes'], true));
        Assert::assertEquals($expectedRawValues, json_decode($draft['raw_values'], true));
    }

    private function createProduct(string $identifier, array $userIntents): void
    {
        $this->get('akeneo_integration_tests.helper.authenticator')->logIn('peter');
        $command = UpsertProductCommand::createWithIdentifier(
            userId: $this->getUserId('peter'),
            productIdentifier: ProductIdentifier::fromIdentifier($identifier),
            userIntents: $userIntents
        );
        $this->get('pim_enrich.product.message_bus')->dispatch($command);
        $this->getContainer()->get('pim_catalog.validator.unique_value_set')->reset();
        $this->get('akeneo.pim.storage_utils.cache.cached_queries_clearer')->clear();
        $this->get('pim_connector.doctrine.cache_clearer')->clear();
    }

    private function createAttribute(string $code, array $data = []): AttributeInterface
    {
        $defaultData = [
            'code' => $code,
            'type' => AttributeTypes::TEXT,
            'group' => 'other',
        ];
        $data = array_merge($defaultData, $data);

        $attribute = $this->get('akeneo_integration_tests.base.attribute.builder')->build($data, true);
        $violations = $this->get('validator')->validate($attribute);
        Assert::assertSame(0, $violations->count(), (string)$violations);
        $this->get('pim_catalog.saver.attribute')->save($attribute);

        return $attribute;
    }

    private function createProductDraft(string $productUuid, array $changes, array $rawValues)
    {
        $sql = <<<SQL
            INSERT INTO pimee_workflow_product_draft (product_uuid, created_at, changes, raw_values, status, source, source_label, author, author_label) 
            VALUES (
                UUID_TO_BIN(:uuid),
                NOW(),
                :changes,
                :raw_values,
                1,
                'pim',
                'PIM',
                'julien',
                'Julien Février'
            );
        SQL;

        $this->connection->executeQuery(
            $sql,
            ['uuid' => $productUuid, 'changes' => $changes, 'raw_values' => $rawValues],
            ['changes' => Types::JSON, 'raw_values' => Types::JSON]
        );
    }

    private function getProductDraft(): array
    {
        $sql = <<<SQL
            SELECT *
            FROM pimee_workflow_product_draft
        SQL;

        return $this->connection->fetchAssociative($sql);
    }
}
