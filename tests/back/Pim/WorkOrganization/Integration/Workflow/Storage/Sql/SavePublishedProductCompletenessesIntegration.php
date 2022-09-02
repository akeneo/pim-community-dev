<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace AkeneoTestEnterprise\Pim\WorkOrganization\Integration\Workflow\Storage\Sql;

use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\Projection\PublishedProductCompleteness;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\Projection\PublishedProductCompletenessCollection;
use Akeneo\Test\Integration\TestCase;
use PHPUnit\Framework\Assert;

/**
 * @author Mathias METAYER <mathias.metayer@akeneo.com>
 */
class SavePublishedProductCompletenessesIntegration extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->get('feature_flags')->enable('published_product');
    }

    public function test_that_it_clears_existing_completenesses_and_missing_attributes_if_provided_completenesses_are_empty(
    )
    {
        $publishedProductId = $this->createPublishedProduct('a_great_product');
        Assert::assertNotEmpty($this->getCompletenessesFromDB($publishedProductId));
        Assert::assertNotEmpty($this->getMissingAttributesFromDb($publishedProductId));

        $this->executeSave(new PublishedProductCompletenessCollection($publishedProductId, []));
        Assert::assertEmpty($this->getCompletenessesFromDB($publishedProductId));
        Assert::assertEmpty($this->getMissingAttributesFromDb($publishedProductId));
    }

    public function test_that_it_saves_completenesses_given_a_product_id()
    {
        $publishedProductId = $this->createPublishedProduct('a_great_product');
        $collection = new PublishedProductCompletenessCollection(
            $publishedProductId, [
            new PublishedProductCompleteness('ecommerce', 'en_US', 5, [])
        ]
        );
        $this->executeSave($collection);

        $dbCompletenesses = $this->getCompletenessesFromDB($publishedProductId);
        Assert::assertCount(1, $dbCompletenesses);
        Assert::assertEquals(
            [
                'channel_code' => 'ecommerce',
                'locale_code' => 'en_US',
                'missing_count' => 0,
                'required_count' => 5,
            ],
            $dbCompletenesses['ecommerce-en_US']
        );
        Assert::assertEmpty($this->getMissingAttributesFromDb($publishedProductId));
    }

    public function test_that_it_saves_completenesses_and_missing_attributes()
    {
        $publishedProductId = $this->createPublishedProduct('a_great_product');

        $collection = new PublishedProductCompletenessCollection(
            $publishedProductId, [
            new PublishedProductCompleteness('ecommerce', 'en_US', 5, ['a_text']),
            new PublishedProductCompleteness(
                'tablet',
                'fr_FR',
                10,
                [
                    'a_localized_and_scopable_text_area',
                    'a_yes_no',
                    'a_multi_select',
                    'a_file',
                    'a_price',
                    'a_number_float',
                ]
            ),
        ]
        );

        $this->executeSave($collection);

        $dbCompletenesses = $this->getCompletenessesFromDB($publishedProductId);
        Assert::assertCount(2, $dbCompletenesses);
        Assert::assertEquals(
            [
                'channel_code' => 'ecommerce',
                'locale_code' => 'en_US',
                'missing_count' => 1,
                'required_count' => 5,
            ],
            $dbCompletenesses['ecommerce-en_US']
        );
        Assert::assertEquals(
            [
                'channel_code' => 'tablet',
                'locale_code' => 'fr_FR',
                'missing_count' => 6,
                'required_count' => 10,
            ],
            $dbCompletenesses['tablet-fr_FR']
        );

        $missingAttributeCodesFromDb = $this->getMissingAttributesFromDb($publishedProductId);
        Assert:
        self::assertCount(2, $missingAttributeCodesFromDb);

        Assert::assertEquals(['a_text'], $missingAttributeCodesFromDb['ecommerce-en_US']);
        Assert::assertEqualsCanonicalizing(
            [
                'a_localized_and_scopable_text_area',
                'a_yes_no',
                'a_multi_select',
                'a_file',
                'a_price',
                'a_number_float',
            ],
            $missingAttributeCodesFromDb['tablet-fr_FR']
        );
    }

    protected function getConfiguration()
    {
        return $this->catalog->useTechnicalCatalog();
    }

    private function executeSave(PublishedProductCompletenessCollection $completenesses): void
    {
        $this->get('pimee_workflow.query.save_published_product_completenesses')->save($completenesses);
    }

    private function createPublishedProduct(string $identifier): int
    {
        $product = $this->get('pim_catalog.builder.product')->createProduct($identifier, 'familyA');
        $this->get('pim_catalog.saver.product')->save($product);

        $publishedProduct = $this->get('pimee_workflow.manager.published_product')->publish($product);

        return $publishedProduct->getId();
    }

    private function getCompletenessesFromDB(int $publishedProductId): array
    {
        $sql = <<<SQL
SELECT channel.code as channel_code, locale.code as locale_code, completeness.missing_count, completeness.required_count
FROM pimee_workflow_published_product_completeness completeness
    INNER JOIN pim_catalog_channel channel on channel.id = completeness.channel_id
    INNER JOIN pim_catalog_locale locale on locale.id = completeness.locale_id
WHERE product_id = :publishedProductId
SQL;
        $results = [];
        $rows = $this->get('database_connection')->executeQuery($sql, ['publishedProductId' => $publishedProductId])->fetchAll();
        foreach ($rows as $row) {
            $key = sprintf('%s-%s', $row['channel_code'], $row['locale_code']);
            $results[$key] = $row;
        }

        return $results;
    }

    private function getMissingAttributesFromDb(int $publishedProductId): array
    {
        $sql = <<<SQL
SELECT channel.code as channel_code, locale.code as locale_code, attribute.code as missing_attribute_code
FROM pimee_workflow_published_product_completeness completeness
    INNER JOIN pim_catalog_channel channel ON channel.id = completeness.channel_id
    INNER JOIN pim_catalog_locale locale ON locale.id = completeness.locale_id
    INNER JOIN pimee_workflow_published_product_completeness_missing_attribute missing_attribute ON completeness.id = missing_attribute.completeness_id
    INNER JOIN pim_catalog_attribute attribute on missing_attribute.missing_attribute_id = attribute.id
WHERE completeness.product_id = :publishedProductId
SQL;
        $results = [];
        $rows = $this->get('database_connection')->executeQuery($sql, ['publishedProductId' => $publishedProductId])->fetchAll();

        foreach ($rows as $row) {
            $key = sprintf('%s-%s', $row['channel_code'], $row['locale_code']);
            $results[$key][] = $row['missing_attribute_code'];
        }

        return $results;
    }
}
