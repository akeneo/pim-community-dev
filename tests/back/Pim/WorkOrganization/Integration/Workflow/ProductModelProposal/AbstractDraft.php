<?php

declare(strict_types=1);

namespace AkeneoTestEnterprise\Pim\WorkOrganization\Integration\Workflow\ProductModelProposal;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Test\Integration\TestCase;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

class AbstractDraft extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $user = $this->get('pim_user.repository.user')->findOneByIdentifier('mary');
        $token = new UsernamePasswordToken($user, null, 'main', $user->getRoles());
        $this->get('security.token_storage')->setToken($token);

        // define redactor with only edit permission on "tshirts" category
        $category = $this->get('pim_catalog.repository.category')->findOneByIdentifier('tshirts');
        $groupRepository = $this->get('pim_user.repository.group');
        $redactorGroup = $groupRepository->findOneByIdentifier('redactor');
        $this->get('pimee_security.manager.category_access')->setAccess($category, [], [$redactorGroup], []);
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration()
    {
        return $this->catalog->useFunctionalCatalog('catalog_modeling');
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
}
