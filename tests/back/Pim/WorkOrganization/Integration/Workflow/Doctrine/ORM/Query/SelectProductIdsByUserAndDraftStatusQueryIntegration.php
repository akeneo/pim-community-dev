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

namespace AkeneoTestEnterprise\Pim\WorkOrganization\Integration\Workflow\Doctrine\ORM\Query;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\EntityWithValuesDraftInterface;
use Akeneo\Test\Integration\TestCase;

class SelectProductIdsByUserAndDraftStatusQueryIntegration extends TestCase
{
    /**
     * @test
     */
    public function it_fetch_product_ids_by_user_and_draft_status()
    {
        $productWithDraftInProgressA = $this->createProduct('product_with_draft_in_progress_A');
        $productWithDraftInProgressB = $this->createProduct('product_with_draft_in_progress_B');
        $productWithDraftWaitingForApproval = $this->createProduct('product_with_draft_waiting_for_approval');
        $this->createProduct('product_in_working_copy');

        $this->createProductDraft('mary', $productWithDraftInProgressA, EntityWithValuesDraftInterface::IN_PROGRESS);
        $this->createProductDraft('mary', $productWithDraftInProgressB, EntityWithValuesDraftInterface::IN_PROGRESS);
        $this->createProductDraft('mary', $productWithDraftWaitingForApproval, EntityWithValuesDraftInterface::READY);

        $expectedProductIds = [$productWithDraftInProgressA->getId(), $productWithDraftInProgressB->getId()];

        $productIds = $this->get('pimee_workflow.query.select_product_ids_by_user_and_draft_status')->execute(
            'mary',
            [EntityWithValuesDraftInterface::IN_PROGRESS]
        );

        $this->assertEqualsCanonicalizing($expectedProductIds, $productIds);
    }

    /**
     * @test
     */
    public function it_returns_an_empty_array_if_there_is_no_draft()
    {
        $this->createProduct('product_in_working_copy');

        $productIds = $this->get('pimee_workflow.query.select_product_ids_by_user_and_draft_status')->execute(
            'mary',
            [EntityWithValuesDraftInterface::IN_PROGRESS, EntityWithValuesDraftInterface::READY]
        );

        $this->assertEquals([], $productIds);
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration()
    {
        return $this->catalog->useTechnicalCatalog();
    }

    private function createProduct(string $identifier): ProductInterface
    {
        $data = [
            'categories' => ['categoryA'],
            'values'     => [
                'a_text' => [
                    ['data' => 'a text', 'locale' => null, 'scope' => null]
                ]
            ]
        ];

        $product = $this->get('pim_catalog.builder.product')->createProduct($identifier);
        $this->get('pim_catalog.updater.product')->update($product, $data);
        $this->get('pim_catalog.saver.product')->save($product);
        $this->get('akeneo_elasticsearch.client.product_and_product_model')->refreshIndex();

        return $product;
    }

    private function createProductDraft(string $userName, ProductInterface $product, int $draftStatus) : EntityWithValuesDraftInterface
    {
        $this->get('pim_catalog.updater.product')->update($product, ['values' => [
            'a_text' => [
                ['data' => 'an edited text', 'locale' => null, 'scope' => null]
            ]
        ]]);

        $user = $this->get('pim_user.provider.user')->loadUserByUsername($userName);

        $productDraft = $this->get('pimee_workflow.product.builder.draft')->build(
            $product,
            $this->get('Akeneo\Pim\WorkOrganization\Workflow\Component\Factory\PimUserDraftSourceFactory')->createFromUser($user)
        );

        if (EntityWithValuesDraftInterface::READY === $draftStatus) {
            $productDraft->setAllReviewStatuses(EntityWithValuesDraftInterface::CHANGE_TO_REVIEW);
            $productDraft->markAsReady();
        }

        $this->get('pimee_workflow.saver.product_draft')->save($productDraft);

        return $productDraft;
    }
}
