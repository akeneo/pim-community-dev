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
use PHPUnit\Framework\ExpectationFailedException;

class GetPublishedProductCompletenessesIntegration extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->get('feature_flags')->enable('published_product');
    }

    public function test_that_it_returns_completenesseses_given_a_published_product_id()
    {
        $this->createPublishedProduct(
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

        $completenesses = $this->getCompletenesses($this->getPublishedProductId('productA'));
        foreach ($completenesses as $completeness) {
            Assert::assertInstanceOf(PublishedProductCompleteness::class, $completeness);
        }
        // ecommerce + en_US
        // tablet + (en_US, de_DE, fr_FR)
        // ecommerce_china + (en_US, zh_CN)
        Assert::assertCount(6, $completenesses);
        $this->assertCompletenessContains($completenesses, 'ecommerce', 'en_US', 4, ['a_simple_select']);
        $this->assertCompletenessContains(
            $completenesses,
            'tablet',
            'en_US',
            4,
            ['a_simple_select', 'a_localized_and_scopable_text_area']
        );
    }

    public function test_that_it_returns_an_empty_collection_for_a_published_product_without_family()
    {
        $this->createPublishedProduct(
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

        Assert::assertCount(0, $this->getCompletenesses($this->getPublishedProductId('product_without_family')));
    }

    protected function getConfiguration()
    {
        return $this->catalog->useTechnicalCatalog();
    }

    private function createPublishedProduct(string $identifier, ?string $familyCode, array $values): void
    {
        $product = $this->get('pim_catalog.builder.product')->createProduct($identifier, $familyCode);
        $this->get('pim_catalog.updater.product')->update($product, ['values' => $values]);
        $this->get('pim_catalog.saver.product')->save($product);

        $this->get('pimee_workflow.manager.published_product')->publish($product);
    }

    private function getPublishedProductId(string $identifier): ?int
    {
        $publishedProductId = $this->get('database_connection')->executeQuery(
            'SELECT id from pimee_workflow_published_product where identifier = :identifier',
            ['identifier' => $identifier]
        )->fetchColumn();

        return $publishedProductId ? (int)$publishedProductId : null;
    }

    private function getCompletenesses(int $publishedProductId): PublishedProductCompletenessCollection
    {
        return $this->get('pimee_workflow.query.get_published_product_completenesses')
                    ->fromPublishedProductId($publishedProductId);
    }

    private function assertCompletenessContains(
        PublishedProductCompletenessCollection $completenesses,
        string $channelCode,
        string $localeCode,
        int $requiredCount,
        array $missingAttributeCodes
    ): void {
        foreach ($completenesses as $completeness) {
            if ($completeness->channelCode() === $channelCode && $completeness->localeCode() === $localeCode) {
                Assert::assertSame($requiredCount, $completeness->requiredCount());
                Assert::assertEqualsCanonicalizing($missingAttributeCodes, $completeness->missingAttributeCodes());

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
