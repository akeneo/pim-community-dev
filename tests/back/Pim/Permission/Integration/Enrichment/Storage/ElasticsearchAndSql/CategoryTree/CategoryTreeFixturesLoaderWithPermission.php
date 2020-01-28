<?php

declare(strict_types=1);

namespace AkeneoTestEnterprise\Pim\Permission\Integration\Enrichment\Storage\ElasticsearchAndSql\CategoryTree;

use Akeneo\Pim\Enrichment\Component\Product\Builder\ProductBuilderInterface;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductCategoryRepositoryInterface;
use Akeneo\Pim\Permission\Bundle\Manager\CategoryAccessManager;
use Akeneo\Pim\Permission\Component\Attributes;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;
use Akeneo\Tool\Component\StorageUtils\Factory\SimpleFactoryInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Akeneo\UserManagement\Component\Repository\GroupRepositoryInterface;
use Akeneo\UserManagement\Component\Repository\UserRepositoryInterface;
use Doctrine\Common\Persistence\ObjectManager;
use PHPUnit\Framework\Assert;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class CategoryTreeFixturesLoaderWithPermission
{
    /** @var UserRepositoryInterface */
    private $userRepository;

    /** @var GroupRepositoryInterface */
    private $groupRepository;

    /** @var ValidatorInterface */
    private $validator;

    /** @var SaverInterface */
    private $userSaver;

    /** @var CategoryAccessManager */
    private $categoryAccessManager;

    /** @var ObjectManager */
    private $objectManager;

    /** @var SimpleFactoryInterface */
    private $categoryFactory;

    /** @var ObjectUpdaterInterface */
    private $categoryUpdater;

    /** @var SaverInterface */
    private $categorySaver;

    /** @var ProductBuilderInterface */
    private $productBuilder;

    /** @var ObjectUpdaterInterface */
    private $productUpdater;

    /** @var ValidatorInterface */
    private $productValidator;

    /** @var SaverInterface */
    private $productSaver;

    /** @var Client */
    private $esClient;

    /** @var SimpleFactoryInterface */
    private $familyFactory;

    /** @var ObjectUpdaterInterface */
    private $familyUpdater;

    /** @var SaverInterface */
    private $familySaver;

    /** @var SimpleFactoryInterface */
    private $familyVariantFactory;

    /** @var ObjectUpdaterInterface */
    private $familyVariantUpdater;

    /** @var SaverInterface */
    private $familyVariantSaver;

    /** @var SimpleFactoryInterface */
    private $attributeFactory;

    /** @var ObjectUpdaterInterface */
    private $attributeUpdater;

    /** @var SaverInterface */
    private $attributeSaver;

    /** @var SimpleFactoryInterface */
    private $productModelFactory;

    /** @var ObjectUpdaterInterface */
    private $productModelUpdater;

    /** @var SaverInterface */
    private $productModelSaver;

    /** @var ProductCategoryRepositoryInterface */
    private $productCategoryRepository;

    public function __construct(
        UserRepositoryInterface $userRepository,
        GroupRepositoryInterface $groupRepository,
        ValidatorInterface $validator,
        SaverInterface $userSaver,
        CategoryAccessManager $categoryAccessManager,
        ObjectManager $objectManager,
        SimpleFactoryInterface $categoryFactory,
        ObjectUpdaterInterface $categoryUpdater,
        SaverInterface $categorySaver,
        ProductBuilderInterface $productBuilder,
        ObjectUpdaterInterface $productUpdater,
        ValidatorInterface $productValidator,
        SaverInterface $productSaver,
        SimpleFactoryInterface $familyFactory,
        ObjectUpdaterInterface $familyUpdater,
        SaverInterface $familySaver,
        SimpleFactoryInterface $familyVariantFactory,
        ObjectUpdaterInterface $familyVariantUpdater,
        SaverInterface $familyVariantSaver,
        SimpleFactoryInterface $attributeFactory,
        ObjectUpdaterInterface $attributeUpdater,
        SaverInterface $attributeSaver,
        SimpleFactoryInterface $productModelFactory,
        ObjectUpdaterInterface $productModelUpdater,
        SaverInterface $productModelSaver,
        ProductCategoryRepositoryInterface $productCategoryRepository,
        Client $esClient
    ) {
        $this->userRepository = $userRepository;
        $this->groupRepository = $groupRepository;
        $this->validator = $validator;
        $this->userSaver = $userSaver;
        $this->categoryAccessManager = $categoryAccessManager;
        $this->objectManager = $objectManager;
        $this->categoryFactory = $categoryFactory;
        $this->categoryUpdater = $categoryUpdater;
        $this->categorySaver = $categorySaver;
        $this->productBuilder = $productBuilder;
        $this->productUpdater = $productUpdater;
        $this->productValidator = $productValidator;
        $this->productSaver = $productSaver;
        $this->esClient = $esClient;
        $this->familyFactory = $familyFactory;
        $this->familyUpdater = $familyUpdater;
        $this->familySaver = $familySaver;
        $this->familyVariantFactory = $familyVariantFactory;
        $this->familyVariantUpdater = $familyVariantUpdater;
        $this->familyVariantSaver = $familyVariantSaver;
        $this->attributeFactory = $attributeFactory;
        $this->attributeUpdater = $attributeUpdater;
        $this->attributeSaver = $attributeSaver;
        $this->productModelFactory = $productModelFactory;
        $this->productModelUpdater = $productModelUpdater;
        $this->productModelSaver = $productModelSaver;
        $this->productCategoryRepository = $productCategoryRepository;
    }

    /**
     * In order to test correctly the join access tables for permission, we put the admin user in several user groups.
     */
    public function adminUserAsRedactorAndITSupport(): void
    {
        $adminUser = $this->userRepository->findOneByIdentifier('admin');
        $redactorGroup = $this->groupRepository->findOneByIdentifier('redactor');
        $adminUser->addGroup($redactorGroup);
        $errors = $this->validator->validate(($adminUser));
        Assert::assertEquals(0, $errors->count());

        $this->userSaver->save($adminUser);
    }

    /**
     * @param array       $categories
     * @param null|string $parentCode
     */
    public function givenTheCategoryTreesWithoutViewPermission(array $categories, ?string $parentCode = null): void
    {
        $userGroup = $this->groupRepository->findOneByIdentifier('Manager');

        foreach ($categories as $categoryCode => $children) {
            $category = $this->categoryFactory->create();
            $this->categoryUpdater->update($category, [
                'code' => $categoryCode,
                'parent' => $parentCode ?? null,
                'labels' => ['en_US' => ucfirst($categoryCode)]
            ]);
            Assert::assertEquals(0, $this->validator->validate($category)->count());
            $this->categorySaver->save($category);

            $this->categoryAccessManager->revokeAccess($category);
            $this->objectManager->flush($category);
            $this->categoryAccessManager->grantAccess($category, $userGroup, Attributes::VIEW_ITEMS);

            $this->givenTheCategoryTreesWithoutViewPermission($children, $categoryCode);
        }
    }

    /**
     * @param array $products
     */
    public function givenTheProductsWithCategories(array $products): void
    {
        foreach ($products as $identifier => $categories) {
            $product = $this->productBuilder->createProduct($identifier);
            $this->productUpdater->update($product, [
                'categories' => $categories
            ]);
            $constraintList = $this->productValidator->validate($product);
            Assert::assertEquals(0, $constraintList->count());
            $this->productSaver->save($product);
        }

        $this->esClient->refreshIndex();
    }

    private function createFamily(): void
    {
        $this->createAttribute([
            'code'              => 'name',
            'type'              => 'pim_catalog_text',
            'localizable'       => false,
            'scopable'          => false,
        ]);
        $family = $this->familyFactory->create();
        $this->familyUpdater->update($family, [
            'code'        => 'family_for_pm',
            'attributes'  => ['sku', 'name']
        ]);
        $this->familySaver->save($family);
    }

    private function createFamilyVariant(): void
    {
        $this->createAttribute([
            'code'              => 'size',
            'type'              => 'pim_catalog_boolean',
            'localizable'       => false,
            'scopable'          => false,
        ]);
        $family = $this->familyVariantFactory->create();
        $this->familyVariantUpdater->update($family, [
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
        $this->familyVariantSaver->save($family);
    }
    private function createAttribute(array $data): void
    {
        $attribute = $this->attributeFactory->create();
        $this->attributeUpdater->update($attribute, $data);
        $this->attributeSaver->save($attribute);
    }

    /**
     * @param array $productModels
     */
    public function givenTheProductModelsWithCategories(array $productModels): void
    {
        $this->createFamily();
        $this->createFamilyVariant();

        foreach ($productModels as $identifier => $categories) {
            $productModel = $this->productModelFactory->create();
            $this->productModelUpdater->update($productModel, [
                'categories' => $categories,
                'code' => 'product_model_'.$identifier,
                'family_variant' => 'family_variant',
                'values'  => []
            ]);
            $constraintList = $this->productValidator->validate($productModel);
            Assert::assertEquals(0, $constraintList->count());
            $this->productModelSaver->save($productModel);
        }
        $this->esClient->refreshIndex();
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
        foreach ($categoryCodes as $categoryCode) {
            $category = $this->productCategoryRepository->findOneByIdentifier($categoryCode);
            $itSupportUserGroup = $this->groupRepository->findOneByIdentifier('IT support');
            $redactorUserGroup = $this->groupRepository->findOneByIdentifier('redactor');

            $this->categoryAccessManager->revokeAccess($category);
            $this->objectManager->flush($category);
            $this->categoryAccessManager->grantAccess($category, $itSupportUserGroup, $accessLevel);
            $this->categoryAccessManager->grantAccess($category, $redactorUserGroup, $accessLevel);
        }
    }
}
