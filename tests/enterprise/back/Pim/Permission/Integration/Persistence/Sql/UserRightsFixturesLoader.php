<?php

declare(strict_types=1);

namespace AkeneoTestEnterprise\Pim\Permission\Integration\Persistence\Sql;

use Akeneo\Category\Infrastructure\Component\Model\Category;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Enrichment\Product\API\Command\UpsertProductCommand;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\ChangeParent;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetBooleanValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetCategories;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\UserIntent;
use Akeneo\Pim\Permission\Component\Attributes;
use Akeneo\Category\Infrastructure\Component\Classification\Model\CategoryInterface;
use Akeneo\UserManagement\Component\Model\Group;
use PHPUnit\Framework\Assert;
use Psr\Container\ContainerInterface;

class UserRightsFixturesLoader
{
    /** @var ContainerInterface */
    protected $container;

    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function loadProductAndProductModels(): void
    {
        $this->createCategoryFixtures();

        $rootProductModels = [
            ['code' => 'not_categorized_root_product_model', 'categories' => []],
            ['code' => 'owned_categorized_root_product_model', 'categories' => ['own_category_1', 'edit_category_1']],
            ['code' => 'editable_categorized_root_product_model', 'categories' => ['edit_category_1']],
            ['code' => 'not_viewable_root_product_model', 'categories' => ['category_without_right']],
        ];

        $subProductModels = [
            ['code' => 'not_categorized_sub_product_model', 'categories' => [], 'parent' => 'not_categorized_root_product_model'],
            ['code' => 'owned_categorized_sub_product_model', 'categories' => ['category_without_right', 'own_category_2'], 'parent' => 'owned_categorized_root_product_model'],
            ['code' => 'not_viewable_sub_product_model', 'categories' => [], 'parent' => 'not_viewable_root_product_model'],
        ];

        $variantProducts = [
            'not_categorized_variant_product' => [
                new ChangeParent('not_categorized_sub_product_model'),
                new SetBooleanValue('a_yes_no', null, null, false),
            ],
            'owned_variant_product' => [
                new SetCategories(['view_category_1', 'edit_category_2']),
                new ChangeParent('owned_categorized_sub_product_model'),
                new SetBooleanValue('a_yes_no', null, null, false),
            ],
            'editable_variant_product' => [
                new SetCategories(['edit_category_2']),
                new ChangeParent('not_categorized_sub_product_model'),
                new SetBooleanValue('a_yes_no', null, null, true),
            ],
            'not_viewable_variant_product' => [
                new ChangeParent('not_viewable_sub_product_model'),
                new SetBooleanValue('a_yes_no', null, null, false),
            ],
        ];

        foreach ($rootProductModels as $rootProductModel) {
            $rootProductModel += [
                'family_variant' => 'familyVariantA1',
                'values'  => [
                    'a_price'  => [
                        'data' => ['data' => [['amount' => '50', 'currency' => 'EUR']], 'locale' => null, 'scope' => null],
                    ],
                    'a_number_float'  => [['data' => '12.5', 'locale' => null, 'scope' => null]],
                    'a_localized_and_scopable_text_area'  => [['data' => 'my pink tshirt', 'locale' => 'en_US', 'scope' => 'ecommerce']],
                ]
            ];

            $this->createProductModel($rootProductModel);
        }

        foreach ($subProductModels as $subProductModel) {
            $subProductModel += [
                'family_variant' => 'familyVariantA1',
                'values'  => [
                    'a_simple_select' => [
                        ['locale' => null, 'scope' => null, 'data' => 'optionB'],
                    ],
                ],
            ];

            $this->createProductModel($subProductModel);
        }

        foreach ($variantProducts as $identifier => $userIntents) {
            $this->createProduct($identifier, $userIntents);
        }
    }

    private function createCategoryFixtures(): void
    {
        $this->createCategory(['code' => 'category_without_right', 'parent' => 'master']);
        $this->createCategory(['code' => 'view_category_1', 'parent' => 'master']);
        $this->createCategory(['code' => 'view_category_2', 'parent' => 'master']);
        $this->createCategory(['code' => 'edit_category_1', 'parent' => 'master']);
        $this->createCategory(['code' => 'edit_category_2', 'parent' => 'master']);
        $this->createCategory(['code' => 'own_category_1', 'parent' => 'master']);
        $this->createCategory(['code' => 'own_category_2', 'parent' => 'master']);

        $this->revokeCategoryAccesses('category_without_right');
        $this->revokeCategoryAccesses('view_category_1');
        $this->revokeCategoryAccesses('view_category_2');
        $this->revokeCategoryAccesses('edit_category_1');
        $this->revokeCategoryAccesses('edit_category_2');
        $this->revokeCategoryAccesses('own_category_1');
        $this->revokeCategoryAccesses('own_category_2');

        $this->createCategoryAccesses('view_category_1', Attributes::OWN_PRODUCTS, 'IT support');
        $this->createCategoryAccesses('view_category_2', Attributes::OWN_PRODUCTS, 'IT support');
        $this->createCategoryAccesses('edit_category_1', Attributes::OWN_PRODUCTS, 'IT support');
        $this->createCategoryAccesses('edit_category_2', Attributes::OWN_PRODUCTS, 'IT support');
        $this->createCategoryAccesses('own_category_1', Attributes::OWN_PRODUCTS, 'IT support');
        $this->createCategoryAccesses('own_category_2', Attributes::OWN_PRODUCTS, 'IT support');

        $this->createCategoryAccesses('view_category_1', Attributes::VIEW_ITEMS, 'Redactor');
        $this->createCategoryAccesses('view_category_2', Attributes::VIEW_ITEMS, 'Redactor');
        $this->createCategoryAccesses('edit_category_1', Attributes::EDIT_ITEMS, 'Redactor');
        $this->createCategoryAccesses('edit_category_2', Attributes::EDIT_ITEMS, 'Redactor');
        $this->createCategoryAccesses('own_category_1', Attributes::OWN_PRODUCTS, 'Redactor');
        $this->createCategoryAccesses('own_category_2', Attributes::OWN_PRODUCTS, 'Redactor');

        $this->createCategoryAccesses('category_without_right', Attributes::OWN_PRODUCTS, 'IT support');

    }

    /**
     * @param UserIntent[] $userIntents
     * @return ProductInterface
     */
    protected function createProduct(string $identifier, array $userIntents = []) : ProductInterface
    {
        $this->container->get('akeneo_integration_tests.helper.authenticator')->logIn('admin');
        $this->container->get('pim_enrich.product.message_bus')->dispatch(UpsertProductCommand::createFromCollection(
            $this->getUserId('admin'),
            $identifier,
            $userIntents
        ));

        return $this->container->get('pim_catalog.repository.product')->findOneByIdentifier($identifier);
    }

    private function getUserId(string $username): int
    {
        return (int)$this->container->get('database_connection')->fetchOne(
            'SELECT id FROM oro_user WHERE username = :username',
            ['username' => $username]
        );
    }

    /**
     * @param array $data
     *
     * @return ProductModelInterface
     * @throws \Exception
     */
    private function createProductModel(array $data = []) : ProductModelInterface
    {
        $productModel = $this->container->get('pim_catalog.factory.product_model')->create();
        $this->container->get('pim_catalog.updater.product_model')->update($productModel, $data);

        $errors = $this->container->get('pim_catalog.validator.product')->validate($productModel);

        Assert::assertCount(0, $errors);

        $this->container->get('pim_catalog.saver.product_model')->save($productModel);
        $this->container->get('akeneo_elasticsearch.client.product_and_product_model')->refreshIndex();

        return $productModel;
    }

    /**
     * @param array $data
     *
     * @return CategoryInterface
     */
    private function createCategory(array $data = []) : CategoryInterface
    {
        $category = $this->container->get('pim_catalog.factory.category')->create();
        $this->container->get('pim_catalog.updater.category')->update($category, $data);
        $errors = $this->container->get('validator')->validate($category);

        Assert::assertCount(0, $errors);

        $this->container->get('pim_catalog.saver.category')->save($category);

        return $category;
    }

    /**
     * @param string $categoryCode
     * @param string $right
     * @param string $userGroupName
     */
    private function createCategoryAccesses(string $categoryCode, string $right, string $userGroupName): void
    {
        $accessManager = $this->container->get('pimee_security.manager.category_access');
        $entityManager = $this->container->get('doctrine.orm.default_entity_manager');

        $category = $entityManager->getRepository(Category::class)->findOneBy(['code' => $categoryCode]);
        $userGroup = $entityManager->getRepository(Group::class)->findOneBy(['name' => $userGroupName]);

        $entityManager->flush();
        $accessManager->grantAccess($category, $userGroup, $right);
    }

    /**
     * @param string $categoryCode
     */
    private function revokeCategoryAccesses(string $categoryCode): void
    {
        $accessManager = $this->container->get('pimee_security.manager.category_access');
        $entityManager = $this->container->get('doctrine.orm.default_entity_manager');

        $category = $entityManager->getRepository(Category::class)->findOneBy(['code' => $categoryCode]);
        $accessManager->revokeAccess($category);
        $entityManager->flush();
    }
}
