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

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\EntityWithValuesDraftInterface;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\ProductModelDraft;
use Akeneo\Test\Integration\TestCase;
use PHPUnit\Framework\Assert;

class GetWorkflowStatusFromProductModelCodesIntegration extends TestCase
{
    /**
     * @test
     */
    public function it_returns_working_copy_status_for_owned_product_models()
    {
        $this->assertWorkflowStatus(
            'admin',
            [
                'uncategorized',
                'owned_and_viewable_by_manager_only',
                'readonly_for_redactors',
                'without_draft',
                'with_in_progress_draft_from_mary',
                'with_ready_draft_from_mary_and_in_progress_from_kevin',
            ],
            [
                'uncategorized' => 'working_copy',
                'owned_and_viewable_by_manager_only' => 'working_copy',
                'readonly_for_redactors' => 'working_copy',
                'without_draft' => 'working_copy',
                'with_in_progress_draft_from_mary' => 'working_copy',
                'with_ready_draft_from_mary_and_in_progress_from_kevin' => 'working_copy',
            ]
        );
    }

    /**
     * @test
     */
    public function it_returns_working_copy_status_for_product_model_without_draft()
    {
        $this->createProductModel('product_model_without_draft', ['categoryA']);

        $this->assertWorkflowStatus(
            'admin',
            ['without_draft'],
            ['without_draft' => 'working_copy']
        );
        $this->assertWorkflowStatus(
            'mary',
            ['without_draft'],
            ['without_draft' => 'working_copy']
        );
    }

    /**
     * @test
     */
    public function it_does_not_return_status_for_non_viewable_product_models()
    {
        $this->assertWorkflowStatus(
            'mary',
            ['owned_and_viewable_by_manager_only'],
            []
        );
    }

    /**
     * @test
     */
    public function it_returns_readonly_status_for_viewable_product_models()
    {
        $this->assertWorkflowStatus(
            'mary',
            ['readonly_for_redactors'],
            ['readonly_for_redactors' => 'read_only']
        );
    }

    /**
     * @test
     */
    public function it_returns_in_progress_status_for_product_model_with_a_draft_in_progress()
    {
        $this->assertWorkflowStatus(
            'mary',
            ['with_in_progress_draft_from_mary'],
            ['with_in_progress_draft_from_mary' => 'draft_in_progress']
        );

        $this->assertWorkflowStatus(
            'kevin',
            ['with_in_progress_draft_from_mary'],
            ['with_in_progress_draft_from_mary' => 'working_copy']
        );
    }

    /**
     * @test
     */
    public function it_returns_waiting_for_approval_status_for_product_model_with_a_ready_draft()
    {
        $this->assertWorkflowStatus(
            'mary',
            ['with_ready_draft_from_mary_and_in_progress_from_kevin'],
            ['with_ready_draft_from_mary_and_in_progress_from_kevin' => 'proposal_waiting_for_approval']
        );

        $this->assertWorkflowStatus(
            'kevin',
            ['with_ready_draft_from_mary_and_in_progress_from_kevin'],
            ['with_ready_draft_from_mary_and_in_progress_from_kevin' => 'draft_in_progress']
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->createProductModel('uncategorized');
        $this->createProductModel('owned_and_viewable_by_manager_only', ['categoryB']);
        $this->createProductModel('readonly_for_redactors', ['categoryA1']);
        $this->createProductModel('without_draft', ['categoryA']);

        $this->createProductModel('with_in_progress_draft_from_mary', ['categoryA']);
        $this->createProductModelDraft('mary', 'with_in_progress_draft_from_mary');

        $this->createProductModel('with_ready_draft_from_mary_and_in_progress_from_kevin', ['categoryA']);
        $this->createProductModelDraft('mary', 'with_ready_draft_from_mary_and_in_progress_from_kevin', true);
        $this->createProductModelDraft('kevin', 'with_ready_draft_from_mary_and_in_progress_from_kevin');
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration()
    {
        return $this->catalog->useTechnicalCatalog();
    }

    /**
     * @param string $code
     * @param array $categories
     *
     * @return ProductModelInterface
     */
    private function createProductModel(string $code, array $categories = []): ProductModelInterface
    {
        $productModel = $this->get('pim_catalog.factory.product_model')->create();
        $this->get('pim_catalog.updater.product_model')->update(
            $productModel,
            [
                'code' => $code,
                'family_variant' => 'familyVariantA2',
                'categories' => $categories,
            ]
        );

        $errors = $this->get('pim_catalog.validator.product')->validate($productModel);

        Assert::assertCount(0, $errors);

        $this->get('pim_catalog.saver.product_model')->save($productModel);

        return $productModel;
    }

    /**
     * @param string $userName
     * @param string $identifier
     * @param bool $ready
     *
     * @return EntityWithValuesDraftInterface
     */
    private function createProductModelDraft(
        string $userName,
        string $identifier,
        bool $ready = false
    ): EntityWithValuesDraftInterface {
        $productModel = $this->get('pim_catalog.repository.product_model_without_permission')->findOneByIdentifier(
            $identifier
        );

        $this->get('pim_catalog.updater.product_model')->update(
            $productModel,
            [
                'values' => [
                    'a_text_area' => [
                        ['data' => 'Lorem ipsum dolor sit amet', 'locale' => null, 'scope' => null],
                    ],
                ],
            ]
        );

        $productModelDraft = $this->get('pimee_workflow.product_model.builder.draft')->build($productModel, $userName);
        if (true === $ready) {
            $productModelDraft->setAllReviewStatuses(EntityWithValuesDraftInterface::CHANGE_TO_REVIEW);
            $productModelDraft->markAsReady();
        }

        $this->get('pimee_workflow.saver.product_model_draft')->save($productModelDraft);

        return $productModelDraft;
    }

    /**
     * @param string $userName
     * @param array $productModelCodes
     * @param array $expectedStatuses
     */
    private function assertWorkflowStatus(string $userName, array $productModelCodes, array $expectedStatuses): void
    {
        $userId = $this->getUserIdFromName($userName);

        $actualStatuses = $this->get('pimee_workflow.query.get_workflow_status_from_product_model_codes')
                        ->fromProductModelCodes($userId, $productModelCodes);
        Assert::assertEqualsCanonicalizing(
            $expectedStatuses,
            $actualStatuses
        );
    }

    /**
     * @param string $userName
     *
     * @return int
     */
    private function getUserIdFromName(string $userName): int
    {
        return (int)$this->get('database_connection')->fetchColumn(
            'SELECT id FROM oro_user WHERE username = ?',
            [$userName],
            0
        );
    }
}
