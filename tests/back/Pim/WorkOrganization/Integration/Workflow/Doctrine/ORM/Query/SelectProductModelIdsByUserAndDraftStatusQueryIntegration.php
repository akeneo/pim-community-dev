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

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\EntityWithValuesDraftInterface;
use Akeneo\Test\Integration\TestCase;

class SelectProductModelIdsByUserAndDraftStatusQueryIntegration extends TestCase
{
    /**
     * @test
     */
    public function it_fetch_product_model_ids_by_user_and_draft_status()
    {
        $productModelWithDraftInProgress = $this->createProductModel('product_model_with_draft_in_progress');
        $productModelWithDraftWaitingForApprovalA = $this->createProductModel('product_model_with_draft_waiting_for_approval_A');
        $productModelWithDraftWaitingForApprovalB = $this->createProductModel('product_model_with_draft_waiting_for_approval_B');
        $this->createProductModel('product_model_in_working_copy');

        $this->createProductModelDraft('mary', $productModelWithDraftInProgress, EntityWithValuesDraftInterface::IN_PROGRESS);
        $this->createProductModelDraft('mary', $productModelWithDraftWaitingForApprovalA, EntityWithValuesDraftInterface::READY);
        $this->createProductModelDraft('mary', $productModelWithDraftWaitingForApprovalB, EntityWithValuesDraftInterface::READY);

        $expectedProductModelIds = [$productModelWithDraftWaitingForApprovalA->getId(), $productModelWithDraftWaitingForApprovalB->getId()];

        $productModelIds = $this->get('pimee_workflow.query.select_product_model_ids_by_user_and_draft_status')->execute(
            'mary',
            [EntityWithValuesDraftInterface::READY]
        );

        $this->assertEqualsCanonicalizing($expectedProductModelIds, $productModelIds);
    }

    /**
     * @test
     */
    public function it_returns_an_empty_array_if_there_is_no_draft()
    {
        $this->createProductModel('product_model_in_working_copy');

        $productModelIds = $this->get('pimee_workflow.query.select_product_model_ids_by_user_and_draft_status')->execute(
            'mary',
            [EntityWithValuesDraftInterface::IN_PROGRESS, EntityWithValuesDraftInterface::READY]
        );

        $this->assertEquals([], $productModelIds);
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration()
    {
        return $this->catalog->useTechnicalCatalog();
    }

    private function createProductModel(string $code): ProductModelInterface
    {
        $productModel = $this->get('pim_catalog.factory.product_model')->create();
        $this->get('pim_catalog.updater.product_model')->update(
            $productModel,
            [
                'code' => $code,
                'family_variant' => 'familyVariantA2',
                'categories' => ['categoryA'],
                'values'     => [
                    'a_text' => [
                        ['data' => 'a text', 'locale' => null, 'scope' => null]
                    ]
                ]
            ]
        );

        $this->get('pim_catalog.saver.product_model')->save($productModel);

        return $productModel;
    }

    private function createProductModelDraft(string $userName, ProductModelInterface $productModel, int $draftStatus) : EntityWithValuesDraftInterface
    {
        $this->get('pim_catalog.updater.product_model')->update($productModel, ['values' => [
            'a_text' => [
                ['data' => 'an edited text', 'locale' => null, 'scope' => null]
            ]
        ]]);

        $user = $this->get('pim_user.provider.user')->loadUserByUsername($userName);

        $productModelDraft = $this->get('pimee_workflow.product_model.builder.draft')->build(
            $productModel,
            $this->get('Akeneo\Pim\WorkOrganization\Workflow\Component\Factory\PimUserDraftSourceFactory')->createFromUser($user)
        );

        if (EntityWithValuesDraftInterface::READY === $draftStatus) {
            $productModelDraft->setAllReviewStatuses(EntityWithValuesDraftInterface::CHANGE_TO_REVIEW);
            $productModelDraft->markAsReady();
        }

        $this->get('pimee_workflow.saver.product_model_draft')->save($productModelDraft);

        return $productModelDraft;
    }
}
