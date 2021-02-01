<?php

declare(strict_types=1);

namespace AkeneoTestEnterprise\Pim\WorkOrganization\Integration\Workflow\ProductModelProposal;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\EntityWithValuesDraftInterface;
use Akeneo\Test\Integration\TestCase;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

class AbstractDraft extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $watch = $this->get('pim_catalog.repository.product')->findOneByIdentifier('watch');
        $this->get('pim_catalog.updater.product')->update($watch, ['categories' => ['tshirts']]);
        $this->get('pim_catalog.saver.product')->save($watch);

        // define redactor with only edit permission on "tshirts" category
        $category = $this->get('pim_catalog.repository.category')->findOneByIdentifier('tshirts');
        $groupRepository = $this->get('pim_user.repository.group');
        $redactorGroup = $groupRepository->findOneByIdentifier('redactor');
        $itSupportGroup = $groupRepository->findOneByIdentifier('IT support');
        $this->get('pimee_security.manager.category_access')->setAccess($category, [], [$redactorGroup], [$itSupportGroup]);

        // login as Mary
        $user = $this->get('pim_user.repository.user')->findOneByIdentifier('mary');
        $token = new UsernamePasswordToken($user, null, 'main', $user->getRoles());
        $this->get('security.token_storage')->setToken($token);
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration()
    {
        return $this->catalog->useFunctionalCatalog('catalog_modeling');
    }

    protected function createProductDraft(string $identifier, $attributeCode, $value): ProductInterface
    {
        $product = $this->get('pim_catalog.repository.product')->findOneByIdentifier($identifier);
        $collectionAttribute = $this->get('pim_catalog.repository.attribute')->findOneByIdentifier($attributeCode);

        $this->get('pim_catalog.builder.entity_with_values')
            ->addOrReplaceValue($product, $collectionAttribute, null, null, $value);

        $this->get('pimee_workflow.saver.product_delegating')->save($product);

        return $product;
    }

    protected function createProductModelDraft(string $code, $attributeCode, $value): ProductModelInterface
    {
        $productModel = $this->get('pim_catalog.repository.product_model')->findOneByIdentifier($code);
        $collectionAttribute = $this->get('pim_catalog.repository.attribute')->findOneByIdentifier($attributeCode);

        $this->get('pim_catalog.builder.entity_with_values')
            ->addOrReplaceValue($productModel, $collectionAttribute, null, null, $value);

        $this->get('pimee_workflow.saver.product_model_delegating')->save($productModel);

        return $productModel;
    }

    protected function sendProductDraftForApproval(string $author, string $identifier): EntityWithValuesDraftInterface
    {
        $entity = $this->get('pim_catalog.repository.product')->findOneByIdentifier($identifier);
        $draft = $this->get('pimee_workflow.repository.product_draft')->findUserEntityWithValuesDraft($entity, $author);
        $this->get('pimee_workflow.manager.product_draft')->markAsReady($draft);

        return $draft;
    }

    protected function sendProductModelDraftForApproval(string $author, string $identifier): EntityWithValuesDraftInterface
    {
        $entity = $this->get('pim_catalog.repository.product_model')->findOneByIdentifier($identifier);
        $draft = $this->get('pimee_workflow.repository.product_model_draft')->findUserEntityWithValuesDraft($entity, $author);
        $this->get('pimee_workflow.manager.product_model_draft')->markAsReady($draft);

        return $draft;
    }
}
