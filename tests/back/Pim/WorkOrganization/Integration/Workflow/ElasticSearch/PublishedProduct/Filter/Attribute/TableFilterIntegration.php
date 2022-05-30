<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace AkeneoTestEnterprise\Pim\WorkOrganization\Integration\Workflow\ElasticSearch\PublishedProduct\Filter\Attribute;

use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderInterface;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\EntityWithValuesDraftInterface;
use Akeneo\Tool\Component\StorageUtils\Cursor\CursorInterface;
use AkeneoTest\Pim\Enrichment\Integration\PQB\AbstractProductQueryBuilderTestCase;
use PHPUnit\Framework\Assert;

class TableFilterIntegration extends AbstractProductQueryBuilderTestCase
{
    private string $draftIdentifier;

    /** @test */
    public function it_filters_published_products_with_not_empty_table_values(): void
    {
        $pqb = $this->get('pimee_workflow.doctrine.query.published_product_query_builder_factory')->create();

        $result = $this->execute($pqb, [['nutrition', Operators::IS_NOT_EMPTY, null, ['locale' => 'en_US']]]);
        $this->assert($result, ['foo', 'baz']);

        $resultfr_FR = $this->execute($pqb, [['nutrition', Operators::IS_NOT_EMPTY, null, ['locale' => 'fr_FR']]]);
        $this->assert($resultfr_FR, []);
    }

    /** @test */
    public function it_filters_proposals_with_not_empty_table_values(): void
    {
        $pqb = $this->get('pimee_workflow.doctrine.query.proposal_product_and_product_model_query_builder_from_size_factory')->create(['limit' => 10]);

        $result = $this->execute($pqb, [['nutrition', Operators::IS_NOT_EMPTY, null, ['locale' => 'en_US']]]);
        $this->assert($result, [$this->draftIdentifier]);

        // We should get the result because proposal pqb don't care about locale or scope
        $resultfr_FR = $this->execute($pqb, [['nutrition', Operators::IS_NOT_EMPTY, null, ['locale' => 'fr_FR']]]);
        $this->assert($resultfr_FR, [$this->draftIdentifier]);
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->get('feature_flags')->enable('published_product');

        $this->createAttribute([
            'code' => 'nutrition',
            'type' => AttributeTypes::TABLE,
            'localizable' => true,
            'group' => 'other',
            'table_configuration' => [
                [
                    'code' => 'ingredient',
                    'data_type' => 'select',
                    'options' => [
                        ['code' => 'sugar'],
                        ['code' => 'salt'],
                    ],
                ],
                [
                    'code' => 'quantity',
                    'data_type' => 'number',
                ],
            ],
        ]);

        $this->esProductClient = $this->get('akeneo_elasticsearch.client.published_product');
        $publishedProductManager = $this->get('pimee_workflow.manager.published_product');

        $foo = $this->createProduct('foo', [
            'categories' => ['categoryA'],
            'values' => [
                'nutrition' => [
                    [
                        'locale' => 'en_US',
                        'scope' => null,
                        'data' => [['ingredient' => 'sugar', 'quantity' => 10]]
                    ]
                ]
            ]
        ]);
        $publishedProductManager->publish($foo);

        $bar = $this->createProduct('bar', ['enabled' => true]);
        $publishedProductManager->publish($bar);

        $baz = $this->createProduct('baz', [
            'values' => [
                'nutrition' => [
                    [
                        'locale' => 'en_US',
                        'scope' => null,
                        'data' => [['ingredient' => 'salt', 'quantity' => 5]]
                    ]
                ]
            ]
        ]);
        $publishedProductManager->publish($baz);
        $this->esProductClient->refreshIndex();

        // Create a proposal for product $foo
        $this->get('pim_catalog.updater.product')->update(
            $foo,
            [
                'values' => [
                    'nutrition' => [
                        [
                            'locale' => 'en_US',
                            'scope' => null,
                            'data' => [['ingredient' => 'sugar', 'quantity' => 20]]
                        ]
                    ]
                ]
            ]
        );

        $user = $this->get('pim_user.provider.user')->loadUserByUsername('mary');
        $draftSource = $this->get('Akeneo\Pim\WorkOrganization\Workflow\Component\Factory\PimUserDraftSourceFactory')->createFromUser($user);

        $draft = $this->get('pimee_workflow.product.builder.draft')->build($foo, $draftSource);
        Assert::assertNotNull($draft);

        $draft->setAllReviewStatuses(EntityWithValuesDraftInterface::CHANGE_TO_REVIEW);
        $draft->markAsReady();

        $this->get('pimee_workflow.saver.product_draft')->save($draft);
        $this->draftIdentifier = $draft->getIdentifier();

        $this->get('akeneo_elasticsearch.client.product_proposal')->refreshIndex();
    }

    private function execute(ProductQueryBuilderInterface $pqb, array $filters): CursorInterface
    {
        foreach ($filters as $filter) {
            $pqb->addFilter($filter[0], $filter[1], $filter[2], $filter[3]);
        }

        return $pqb->execute();
    }
}
