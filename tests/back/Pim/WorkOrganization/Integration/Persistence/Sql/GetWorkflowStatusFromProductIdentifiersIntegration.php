<?php

namespace AkeneoTestEnterprise\Pim\WorkOrganization\Integration\Persistence\Sql;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\WorkOrganization\Workflow\Bundle\Storage\Sql\GetWorkflowStatusFromProductIdentifiers;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\EntityWithValuesDraftInterface;
use Akeneo\Test\Integration\TestCase;
use PHPUnit\Framework\Assert;

class GetWorkflowStatusFromProductIdentifiersIntegration extends TestCase
{
    /**
     * @test
     */
    public function it_returns_workflow_statuses()
    {
        $product = $this->createProduct('redactor_can_apply_draft_on_product', [
            'categories' => ['categoryA'],
            'values'     => [
                'a_text' => [
                    ['data' => 'a text', 'locale' => null, 'scope' => null]
                ]
            ]
        ]);

        $this->createEntityWithValuesDraft('mary', $product, [
            'values' => [
                'a_text' => [
                    ['data' => 'an edited text', 'locale' => null, 'scope' => null]
                ]
            ]
        ], true);

        $query = $this->getQuery();
        $userId = $this->getUserIdfromName('mary');
        $result = $query->fromProductIdentifiers($userId, [
            'product_without_category',
            'product_owned_by_admin',
            'product_viewable_by_redactor',
            'redactor_can_apply_draft_on_product',
            'product_non_viewable_by_redactor',
        ]);

        Assert::assertArrayNotHasKey('non_viewable_category', $result);
        Assert::assertEqualsCanonicalizing([
            'product_without_category' => 'working_copy',
            'product_owned_by_admin' => 'read_only',
            'product_viewable_by_redactor' => 'read_only',
            'redactor_can_apply_draft_on_product' => 'proposal_waiting_for_approval'
        ], $result);
    }

    /**
     * @test
     */
    public function it_returns_working_copy_status_for_owned_products()
    {
        $query = $this->getQuery();
        $userId = $this->getUserIdfromName('admin');
        $result = $query->fromProductIdentifiers($userId, [
            'product_without_category',
            'product_owned_by_admin'
        ]);

        Assert::assertEqualsCanonicalizing([
            'product_without_category' => 'working_copy',
            'product_owned_by_admin' => 'working_copy'
        ], $result);
    }

    /**
     * @test
     */
    public function it_returns_read_only_status_for_viewable_products()
    {
        $query = $this->getQuery();
        $userId = $this->getUserIdfromName('mary');
        $result = $query->fromProductIdentifiers($userId, [
            'product_viewable_by_redactor'
        ]);

        Assert::assertEqualsCanonicalizing([
            'product_viewable_by_redactor' => 'read_only'
        ], $result);
    }

    /**
     * @test
    */
    public function it_returns_working_copy_status_for_editable_products_without_draft()
    {
        $this->createProduct('redactor_can_apply_draft_on_product', [
            'categories' => ['categoryA']
        ]);

        $query = $this->getQuery();
        $userId = $this->getUserIdfromName('mary');
        $result = $query->fromProductIdentifiers($userId, [
            'redactor_can_apply_draft_on_product'
        ]);

        Assert::assertEqualsCanonicalizing([
            'redactor_can_apply_draft_on_product' => 'working_copy'
        ], $result);
    }

    /**
     * @test
    */
    public function it_returns_waiting_for_approval_status_for_editable_and_ready_products()
    {
        $product = $this->createProduct('redactor_can_apply_draft_on_product', [
            'categories' => ['categoryA'],
            'values'     => [
                'a_text' => [
                    ['data' => 'a text', 'locale' => null, 'scope' => null]
                ]
            ]
        ]);

        $this->createEntityWithValuesDraft('mary', $product, [
            'values' => [
                'a_text' => [
                    ['data' => 'an edited text', 'locale' => null, 'scope' => null]
                ]
            ]
        ], true);

        $query = $this->getQuery();

        $resultForMary = $query->fromProductIdentifiers(
            $this->getUserIdfromName('mary'),
            ['redactor_can_apply_draft_on_product']
        );
        Assert::assertEqualsCanonicalizing([
            'redactor_can_apply_draft_on_product' => 'proposal_waiting_for_approval',
        ], $resultForMary);

        // draft status is relative to the current user
        $resultForKevin = $query->fromProductIdentifiers(
            $this->getUserIdfromName('kevin'),
            ['redactor_can_apply_draft_on_product']
        );
        Assert::assertEqualsCanonicalizing([
            'redactor_can_apply_draft_on_product' => 'working_copy',
        ], $resultForKevin);
    }

    /**
     * @test
     */
    public function it_returns_draft_in_progress_status_for_editable_and_in_progress_products()
    {
        $product = $this->createProduct('redactor_can_apply_draft_on_product', [
            'categories' => ['categoryA'],
            'values'     => [
                'a_text' => [
                    ['data' => 'a text', 'locale' => null, 'scope' => null]
                ]
            ]
        ]);

        $this->createEntityWithValuesDraft('mary', $product, [
            'values' => [
                'a_text' => [
                    ['data' => 'an edited text', 'locale' => null, 'scope' => null]
                ]
            ]
        ]);

        $query = $this->getQuery();

        $resultForMary = $query->fromProductIdentifiers(
            $this->getUserIdfromName('mary'),
            ['redactor_can_apply_draft_on_product']
        );

        Assert::assertEqualsCanonicalizing([
            'redactor_can_apply_draft_on_product' => 'draft_in_progress',
        ], $resultForMary);

        // draft status is relative to the current user
        $resultForKevin = $query->fromProductIdentifiers(
            $this->getUserIdfromName('kevin'),
            ['redactor_can_apply_draft_on_product']
        );
        Assert::assertEqualsCanonicalizing([
            'redactor_can_apply_draft_on_product' => 'working_copy',
        ], $resultForKevin);
    }

    /**
     * @test
     */
    public function it_does_not_return_status_for_product_non_viewable_by_redactors()
    {
        $query = $this->getQuery();
        $userId = $this->getUserIdfromName('admin');
        $result = $query->fromProductIdentifiers($userId, [
            'product_without_category',
            'non_viewable_category'
        ]);

        Assert::assertArrayNotHasKey('non_viewable_category', $result);
        Assert::assertEqualsCanonicalizing([
            'product_without_category' => 'working_copy'
        ], $result);
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->createProduct('product_without_category');
        $this->createProduct('product_owned_by_admin', [
            'categories' => ['categoryA2']
        ]);

        $this->createProduct('product_viewable_by_redactor', [
            'categories' => ['categoryA2']
        ]);

        $this->createProduct('product_non_viewable_by_redactor', [
            'categories' => ['categoryB']
        ]);
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration()
    {
        return $this->catalog->useTechnicalCatalog();
    }

    private function getUserIdfromName(string $userName)
    {
        return $this->get('database_connection')
            ->fetchColumn('SELECT id FROM oro_user WHERE username = ?', [$userName], 0);
    }

    /**
     * @param string $userName
     * @param ProductInterface $product
     * @param array $changes
     * @param bool $ready
     *
     * @return EntityWithValuesDraftInterface
     */
    protected function createEntityWithValuesDraft(
        string $userName,
        ProductInterface $product,
        array $changes,
        bool $ready = false
    ) : EntityWithValuesDraftInterface {
        $this->get('pim_catalog.updater.product')->update($product, $changes);

        $productDraft = $this->get('pimee_workflow.product.builder.draft')->build($product, $userName);


        if (true === $ready) {
            $productDraft->setAllReviewStatuses(EntityWithValuesDraftInterface::CHANGE_TO_REVIEW);
            $productDraft->markAsReady();
        }

        $this->get('pimee_workflow.saver.product_draft')->save($productDraft);

        return $productDraft;
    }


    /**
     * @param string $identifier
     * @param array  $data
     *
     * @return ProductInterface
     */
    private function createProduct($identifier, array $data = [])
    {
        $product = $this->get('pim_catalog.builder.product')->createProduct($identifier);
        $this->get('pim_catalog.updater.product')->update($product, $data);
        $this->get('pim_catalog.saver.product')->save($product);

        $this->get('akeneo_elasticsearch.client.product')->refreshIndex();

        return $product;
    }

    private function getQuery(): GetWorkflowStatusFromProductIdentifiers
    {
        return $this->get('pimee_workflow.query.get_workflow_status_from_product_identifiers');
    }

}
