<?php

declare(strict_types=1);

namespace AkeneoTestEnterprise\Pim\Permission\Integration\Enrichment\Storage\ElasticsearchAndSql\CategoryTree;

use Akeneo\Pim\Permission\Component\Attributes;
use PHPUnit\Framework\Assert;
use Psr\Container\ContainerInterface;

class CategoryTreeFixturesLoaderWithPermission
{
    /** @var  ContainerInterface */
    private $container;

    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * In order to test correctly the join access tables for permission, we put the admin user in several user groups.
     */
    public function adminUserAsRedactorAndITSupport(): void
    {
        $adminUser = $this->container->get('pim_user.repository.user')->findOneByIdentifier('admin');
        $redactorGroup = $this->container->get('pim_user.repository.group')->findOneByIdentifier('redactor');
        $adminUser->addGroup($redactorGroup);
        $errors = $this->container->get('validator')->validate(($adminUser));
        Assert::assertEquals(0, $errors->count());

        $this->container->get('pim_user.saver.user')->save($adminUser);
    }

    /**
     * @param array       $categories
     * @param null|string $parentCode
     */
    public function givenTheCategoryTreesWithoutViewPermission(array $categories, ?string $parentCode = null): void
    {
        $accessManager = $this->container->get('pimee_security.manager.category_access');
        $entityManager = $this->container->get('doctrine.orm.default_entity_manager');
        $groupRepository = $this->container->get('pim_user.repository.group');

        $userGroup = $groupRepository->findOneByIdentifier('Manager');

        foreach ($categories as $categoryCode => $children) {
            $category = $this->container->get('pim_catalog.factory.category')->create();
            $this->container->get('pim_catalog.updater.category')->update($category, [
                'code' => $categoryCode,
                'parent' => $parentCode ?? null,
                'labels' => ['en_US' => ucfirst($categoryCode)]
            ]);
            Assert::assertEquals(0, $this->container->get('validator')->validate($category)->count());
            $this->container->get('pim_catalog.saver.category')->save($category);

            $accessManager->revokeAccess($category);
            $entityManager->flush($category);
            $accessManager->grantAccess($category, $userGroup, Attributes::VIEW_ITEMS);

            $this->givenTheCategoryTreesWithoutViewPermission($children, $categoryCode);
        }
    }

    /**
     * @param array $products
     */
    public function givenTheProductsWithCategories(array $products): void
    {
        foreach ($products as $identifier => $categories) {
            $product = $this->container->get('pim_catalog.builder.product')->createProduct($identifier);
            $this->container->get('pim_catalog.updater.product')->update($product, [
                'categories' => $categories
            ]);
            $constraintList = $this->container->get('pim_catalog.validator.product')->validate($product);
            Assert::assertEquals(0, $constraintList->count());
            $this->container->get('pim_catalog.saver.product')->save($product);
        }

        $this->container->get('akeneo_elasticsearch.client.product_and_product_model')->refreshIndex();
    }

    private function createFamily(): void
    {
        $this->createAttribute([
            'code'              => 'name',
            'type'              => 'pim_catalog_text',
            'localizable'       => false,
            'scopable'          => false,
        ]);
        $family = $this->container->get('pim_catalog.factory.family')->create();
        $this->container->get('pim_catalog.updater.family')->update($family, [
            'code'        => 'family_for_pm',
            'attributes'  => ['sku', 'name']
        ]);
        $this->container->get('pim_catalog.saver.family')->save($family);
    }
    private function createFamilyVariant(): void
    {
        $this->createAttribute([
            'code'              => 'size',
            'type'              => 'pim_catalog_boolean',
            'localizable'       => false,
            'scopable'          => false,
        ]);
        $family = $this->container->get('pim_catalog.factory.family_variant')->create();
        $this->container->get('pim_catalog.updater.family_variant')->update($family, [
            'code'        => 'family_variant',
            'family'      => 'family_for_pm',
            'variant_attribute_sets' => [
                [
                    'level' => 1,
                    'axes' => ['size'],
                    'attributes' => ['size'],
                ],
            ]
        ]);
        $this->container->get('pim_catalog.saver.family_variant')->save($family);
    }
    private function createAttribute(array $data): void
    {
        $attribute = $this->container->get('pim_catalog.factory.attribute')->create();
        $this->container->get('pim_catalog.updater.attribute')->update($attribute, $data);
        $this->container->get('pim_catalog.saver.attribute')->save($attribute);
    }

    /**
     * @param array $productModels
     */
    public function givenTheProductModelsWithCategories(array $productModels): void
    {
        $this->createFamily();
        $this->createFamilyVariant();

        foreach ($productModels as $identifier => $categories) {
            $productModel = $this->container->get('pim_catalog.factory.product_model')->create();
            $this->container->get('pim_catalog.updater.product_model')->update($productModel, [
                'categories' => $categories,
                'code' => 'product_model_'.$identifier,
                'family_variant' => 'family_variant',
                'values'  => []
            ]);
            $constraintList = $this->container->get('pim_catalog.validator.product_model')->validate($productModel);
            Assert::assertEquals(0, $constraintList->count());
            $this->container->get('pim_catalog.saver.product_model')->save($productModel);
        }
        $this->container->get('akeneo_elasticsearch.client.product_and_product_model')->refreshIndex();
    }

    /**
     * @param array $categoryCodes
     */
    public function givenTheViewableCategories(array $categoryCodes): void
    {
        $this->givenTheRightOnCategoryCodes(Attributes::VIEW_ITEMS, $categoryCodes);
    }

    /**
     * @param array $categoryCodes
     */
    public function givenTheOwnableCategories(array $categoryCodes): void
    {
        $this->givenTheRightOnCategoryCodes(Attributes::OWN_PRODUCTS, $categoryCodes);
    }

    /**
     * @param array $categoryCodes
     */
    public function givenTheEditableCategories(array $categoryCodes): void
    {
        $this->givenTheRightOnCategoryCodes(Attributes::EDIT_ITEMS, $categoryCodes);
    }

    private function givenTheRightOnCategoryCodes(string $accessLevel, $categoryCodes): void
    {
        $accessManager = $this->container->get('pimee_security.manager.category_access');
        $entityManager = $this->container->get('doctrine')->getEntityManager();
        $groupRepository = $this->container->get('pim_user.repository.group');
        $categoryRepository = $this->container->get('pim_catalog.repository.product_category');

        foreach ($categoryCodes as $categoryCode) {
            $category = $categoryRepository->findOneByIdentifier($categoryCode);
            $itSupportUserGroup = $groupRepository->findOneByIdentifier('IT support');
            $redactorUserGroup = $groupRepository->findOneByIdentifier('redactor');

            $accessManager->revokeAccess($category);
            $entityManager->flush($category);
            $accessManager->grantAccess($category, $itSupportUserGroup, $accessLevel);
            $accessManager->grantAccess($category, $redactorUserGroup, $accessLevel);
        }
    }
}
