<?php

declare(strict_types=1);

namespace PimEnterprise\Bundle\ApiBundle\tests\integration\Controller;

use Akeneo\Component\Classification\Model\CategoryInterface;
use PHPUnit\Framework\Assert;
use Pim\Component\Catalog\AttributeTypes;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Model\ProductModelInterface;
use Pim\Component\Catalog\Model\VariantProductInterface;
use PimEnterprise\Component\Security\Attributes;
use Psr\Container\ContainerInterface;

/**
 * @author    Alexandre Hocquard <alexandre.hocquard@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class PermissionFixturesLoader
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

    /**
     * For redactor user:
     *
     * +----------+--------------------------------------------+
     * |                     Variant products                  |
     * +----------+--------------------------------------------+
     * | Own      | variant_product                            |
     * +----------+--------------------------------------------+
     *
     * +----------+--------------------------------------------+
     * |                     Variant products                  |
     * +----------+--------------------------------------------+
     * | No view  | product_no_view                            |
     * +----------+--------------------------------------------+
     * | View     | product_view                               |
     * +----------+--------------------------------------------+
     * | Own      | product_own                                |
     * +----------+--------------------------------------------+
     *
     * +-------------------------------------------------------+
     * |                     Categories                        |
     * +-------------------------------------------------------+
     * | No view  | category_without_right                     |
     * +-------------------------------------------------------+
     * | View     | view_category, edit_category, own_category |
     * +-------------------------------------------------------+
     * | Edit     | edit_category                              |
     * +-------------------------------------------------------+
     * | Own      | own_category                               |
     * +-------------------------------------------------------+
     *
     * +-------------------------------------------------------+
     * |                    Attributes                         |
     * +----------+--------------------------------------------+
     * | No view  | root_product_model_no_view_attribute       |
     * |          | sub_product_model_no_view_attribute        |
     * |          | variant_product_no_view_attribute          |
     * +----------+--------------------------------------------+
     */
    public function loadProductModelsForAssociationPermissions(): void
    {
        $this->createCategoryFixtures();
        $this->createAttributeFixtures();
        $this->createFamilyVariant();

        $rootProductModel = [
            'code' => 'root_product_model',
            'family_variant' => 'family_variant_permission',
            'categories' => ['own_category'],
            'values' => [
                'root_product_model_no_view_attribute' => [
                    ['locale' => 'fr_FR', 'scope' => null, 'data' => true],
                ],
            ]
        ];

        $subProductModel = [
            'code' => 'sub_product_model',
            'parent' => 'root_product_model',
            'values' => [
                'sub_product_model_axis_attribute' => [
                    ['locale' => null, 'scope' => null, 'data' => true],
                ],
            ]
        ];

        $variantProduct = [
            'parent' => 'sub_product_model',
            'values' => [
                'variant_product_axis_attribute' => [
                    ['locale' => null, 'scope' => null, 'data' => true],
                ],
            ],
            'associations' => [
                'X_SELL' => [
                    'products' => ['product_no_view', 'product_view']
                ]
            ]
        ];

        $productNoView = [
            'categories' => ['category_without_right'],
        ];

        $productView = [
            'categories' => ['view_category'],
        ];

        $productOwn = [
            'categories' => ['own_category'],
        ];

        $this->createProductModel($rootProductModel);
        $this->createProductModel($subProductModel);
        $this->createProduct('product_no_view', $productNoView);
        $this->createProduct('product_view', $productView);
        $this->createProduct('product_own', $productOwn);
        $this->createVariantProduct('variant_product', $variantProduct);
    }

    /**
     * For redactor user:
     *
     * +-------------------------------------------------------+
     * |                     Categories                        |
     * +-------------------------------------------------------+
     * | No view  | category_without_right                     |
     * +-------------------------------------------------------+
     * | View     | view_category, edit_category, own_category |
     * +-------------------------------------------------------+
     * | Edit     | edit_category                              |
     * +-------------------------------------------------------+
     * | Own      | own_category                               |
     * +-------------------------------------------------------+
     *
     * +----------+--------------------------------------------+
     * |                     Locales                           |
     * +----------+--------------------------------------------+
     * | No View  | de_DE                                      |
     * +----------+--------------------------------------------+
     * | View     | fr_FR, en_US                               |
     * +----------+--------------------------------------------+
     * | Edit     | en_US                                      |
     * +----------+--------------------------------------------+
     *
     * +-------------------------------------------------------+
     * |                    Attributes                         |
     * +----------+--------------------------------------------+
     * | No view  | root_product_model_no_view_attribute       |
     * |          | sub_product_model_no_view_attribute        |
     * |          | variant_product_no_view_attribute          |
     * +----------+--------------------------------------------+
     * | View     | root_product_model_view_attribute          |
     * |          | root_product_model_edit_attribute          |
     * |          | sub_product_model_view_attribute           |
     * |          | sub_product_model_edit_attribute           |
     * |          | variant_product_view_attribute             |
     * |          | variant_product_edit_attribute             |
     * +-------------------------------------------------------+
     * | Edit     | root_product_model_edit_attribute          |
     * |          | sub_product_model_edit_attribute           |
     * |          | variant_product_edit_attribute             |
     * +----------+--------------------------------------------+
     */
    public function loadProductModelsFixturesForAttributeAndLocalePermissions(): void
    {
        $this->createCategoryFixtures();
        $this->createAttributeFixtures();
        $this->createFamilyVariant();

        $rootProductModel = [
            'code' => 'root_product_model',
            'family_variant' => 'family_variant_permission',
            'categories' => ['own_category'],
            'values' => [
                'root_product_model_no_view_attribute' => [
                    ['locale' => 'en_US', 'scope' => null, 'data' => true],
                    ['locale' => 'fr_FR', 'scope' => null, 'data' => true],
                    ['locale' => 'de_DE', 'scope' => null, 'data' => true],                ],
                'root_product_model_view_attribute' => [
                    ['locale' => 'en_US', 'scope' => null, 'data' => true],
                    ['locale' => 'fr_FR', 'scope' => null, 'data' => true],
                    ['locale' => 'de_DE', 'scope' => null, 'data' => true],
                ],
                'root_product_model_edit_attribute' => [
                    ['locale' => 'en_US', 'scope' => null, 'data' => true],
                    ['locale' => 'fr_FR', 'scope' => null, 'data' => true],
                    ['locale' => 'de_DE', 'scope' => null, 'data' => true],
                ],
            ]
        ];

        $subProductModel = [
            'code' => 'sub_product_model',
            'parent' => 'root_product_model',
            'values' => [
                'sub_product_model_axis_attribute' => [
                    ['locale' => null, 'scope' => null, 'data' => true],
                ],
                'sub_product_model_no_view_attribute' => [
                    ['locale' => 'en_US', 'scope' => null, 'data' => true],
                    ['locale' => 'fr_FR', 'scope' => null, 'data' => true],
                    ['locale' => 'de_DE', 'scope' => null, 'data' => true],
                ],
                'sub_product_model_view_attribute' => [
                    ['locale' => 'en_US', 'scope' => null, 'data' => true],
                    ['locale' => 'fr_FR', 'scope' => null, 'data' => true],
                    ['locale' => 'de_DE', 'scope' => null, 'data' => true],
                ],
                'sub_product_model_edit_attribute' => [
                    ['locale' => 'en_US', 'scope' => null, 'data' => true],
                    ['locale' => 'fr_FR', 'scope' => null, 'data' => true],
                    ['locale' => 'de_DE', 'scope' => null, 'data' => true],
                ],
            ]
        ];

        $variantProduct = [
            'parent' => 'sub_product_model',
            'values' => [
                'variant_product_axis_attribute' => [
                    ['locale' => null, 'scope' => null, 'data' => true],
                ],
                'variant_product_no_view_attribute' => [
                    ['locale' => 'en_US', 'scope' => null, 'data' => true],
                    ['locale' => 'fr_FR', 'scope' => null, 'data' => true],
                    ['locale' => 'de_DE', 'scope' => null, 'data' => true],
                ],
                'variant_product_view_attribute' => [
                    ['locale' => 'en_US', 'scope' => null, 'data' => true],
                    ['locale' => 'fr_FR', 'scope' => null, 'data' => true],
                    ['locale' => 'de_DE', 'scope' => null, 'data' => true],
                ],
                'variant_product_edit_attribute' => [
                    ['locale' => 'en_US', 'scope' => null, 'data' => true],
                    ['locale' => 'fr_FR', 'scope' => null, 'data' => true],
                    ['locale' => 'de_DE', 'scope' => null, 'data' => true],
                ],
            ]
        ];

        $this->createProductModel($rootProductModel);
        $this->createProductModel($subProductModel);
        $this->createVariantProduct('variant_product', $variantProduct);
    }


    /**
     * For redactor user:
     *
     * +-------------------------------------------------------+
     * |                    Product models                     |
     * +-------------------------------------------------------+
     * | No view  | sweat_no_view                              |
     * |          | colored_sweat_no_view                      |
     * |          | shoes_no_view                              |
     * |          | jacket_no_view                             |
     * +-------------------------------------------------------+
     * | View     | shoes_view                                 |
     * |          | tshirt_view                                |
     * |          | sweat_edit                                 |
     * |          | shoes_own                                  |
     * |          | trousers                                   |
     * |          | colored_shoes_view                         |
     * |          | colored_tshirt_view                        |
     * |          | colored_sweat_edit                         |
     * |          | colored_shoes_edit                         |
     * |          | colored_jacket_own                         |
     * |          | colored_shoes_own                          |
     * |          | colored_trousers                           |
     * +-------------------------------------------------------+
     * | Edit     | sweat_edit                                 |
     * |          | colored_sweat_edit                         |
     * |          | colored_shoes_edit                         |
     * +-------------------------------------------------------+
     * | Own      | shoes_own                                  |
     * |          | trousers                                   |
     * |          | colored_jacket_own                         |
     * |          | colored_shoes_own                          |
     * |          | colored_trousers                           |
     * +-------------------------------------------------------+
     *
     * +----------+--------------------------------------------+
     * |                     Variant products                  |
     * +----------+--------------------------------------------+
     * | No View  | colored_sized_sweat_no_view                |
     * +----------+--------------------------------------------+
     * | View     | colored_sized_shoes_view                   |
     * |          | colored_sized_tshirt_view                  |
     * |          | colored_sized_tshirt_view                  |
     * |          | colored_sized_sweat_edit                   |
     * |          | colored_sized_shoes_edit                   |
     * |          | colored_sized_sweat_own                    |
     * |          | colored_sized_shoes_own                    |
     * |          | colored_sized_trousers                     |
     * +----------+--------------------------------------------+
     * | Edit     | colored_sized_sweat_edit                   |
     * |          | colored_sized_shoes_edit                   |
     * +----------+--------------------------------------------+
     * | Own      | colored_sized_sweat_own                    |
     * |          | colored_sized_shoes_own                    |
     * |          | colored_sized_trousers                     |
     * +----------+--------------------------------------------+
     *
     * +-------------------------------------------------------+
     * |                     Categories                        |
     * +-------------------------------------------------------+
     * | No view  | category_without_right                     |
     * +-------------------------------------------------------+
     * | View     | view_category, edit_category, own_category |
     * +-------------------------------------------------------+
     * | Edit     | edit_category                              |
     * +-------------------------------------------------------+
     * | Own      | own_category                               |
     * +-------------------------------------------------------+
     */
    public function loadProductModelsFixturesForCategoryPermissions(): void
    {
        $this->createCategoryFixtures();
        $this->createAttributeFixtures();

        $rootProductModels = [
            ['code' => 'sweat_no_view', 'categories' => ['category_without_right']],
            ['code' => 'jacket_no_view', 'categories' => ['category_without_right']],
            ['code' => 'shoes_view', 'categories' => ['view_category']],
            ['code' => 'tshirt_view', 'categories' => ['view_category']],
            ['code' => 'sweat_edit', 'categories' => ['edit_category']],
            ['code' => 'shoes_no_view', 'categories' => ['category_without_right']],
            ['code' => 'shoes_own', 'categories' => ['own_category']],
            ['code' => 'trousers', 'categories' => []]
        ];

        $subProductModels = [
            ['code' => 'colored_sweat_no_view', 'categories' => [], 'parent' => 'sweat_no_view'],
            ['code' => 'colored_shoes_view', 'categories' => ['category_without_right'], 'parent' => 'shoes_view'],
            ['code' => 'colored_tshirt_view', 'categories' => ['view_category'], 'parent' => 'tshirt_view'],
            ['code' => 'colored_sweat_edit', 'categories' => ['category_without_right'], 'parent' => 'sweat_edit'],
            ['code' => 'colored_shoes_edit', 'categories' => ['edit_category'], 'parent' => 'shoes_no_view'],
            ['code' => 'colored_jacket_own', 'categories' => ['own_category'], 'parent' => 'jacket_no_view'],
            ['code' => 'colored_shoes_own', 'categories' => ['category_without_right'], 'parent' => 'shoes_own'],
            ['code' => 'colored_trousers', 'parent' => 'trousers']
        ];

        $variantProducts = [
            'colored_sized_sweat_no_view' => ['categories' => ['category_without_right'], 'parent' => 'colored_sweat_no_view'],
            'colored_sized_shoes_view' => ['categories' => ['category_without_right'], 'parent' => 'colored_shoes_view'],
            'colored_sized_tshirt_view' => ['categories' => ['view_category'], 'parent' => 'colored_tshirt_view'],
            'colored_sized_sweat_edit' => ['categories' => ['category_without_right'], 'parent' => 'colored_sweat_edit'],
            'colored_sized_shoes_edit' => ['categories' => ['edit_category'], 'parent' => 'colored_shoes_edit'],
            'colored_sized_sweat_own' => ['categories' => ['category_without_right'], 'parent' => 'colored_jacket_own'],
            'colored_sized_shoes_own' => ['categories' => ['own_category'], 'parent' => 'colored_shoes_own'],
            'colored_sized_trousers' => ['parent' => 'colored_trousers'],
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

        foreach ($variantProducts as $identifier => $data) {
            $data += [
                'values'  => [
                    'a_yes_no' => [
                        ['locale' => null, 'scope' => null, 'data' => false],
                    ],
                ],
            ];

            $this->createVariantProduct($identifier, $data);
        }
    }

    private function createCategoryFixtures(): void
    {
        $this->createCategory(['code' => 'category_without_right', 'parent' => 'master']);
        $this->createCategory(['code' => 'view_category', 'parent' => 'master']);
        $this->createCategory(['code' => 'edit_category', 'parent' => 'master']);
        $this->createCategory(['code' => 'own_category', 'parent' => 'master']);

        $this->revokeCategoryAccesses('category_without_right');
        $this->createCategoryAccesses('view_category', Attributes::OWN_PRODUCTS, 'IT support');
        $this->createCategoryAccesses('edit_category', Attributes::OWN_PRODUCTS, 'IT support');
        $this->createCategoryAccesses('own_category', Attributes::OWN_PRODUCTS, 'IT support');
        $this->createCategoryAccesses('view_category', Attributes::VIEW_ITEMS, 'Redactor');
        $this->createCategoryAccesses('edit_category', Attributes::EDIT_ITEMS, 'Redactor');
        $this->createCategoryAccesses('own_category', Attributes::OWN_PRODUCTS, 'Redactor');

        $rights = [
            'view_category' => Attributes::VIEW_ITEMS,
            'edit_category' => Attributes::EDIT_ITEMS,
            'own_category' => Attributes::OWN_PRODUCTS,
        ];

        $this->createCategoryAccesses('category_without_right', Attributes::OWN_PRODUCTS, 'IT support');

        foreach ($rights as $categoryCode => $right) {
            $this->createCategoryAccesses($categoryCode, Attributes::OWN_PRODUCTS, 'IT support');
            $this->createCategoryAccesses($categoryCode, $right, 'Redactor');
        }
    }

    private function createAttributeFixtures(): void
    {
        $this->createAttribute('root_product_model_no_view_attribute', 'none');
        $this->createAttribute('root_product_model_view_attribute', 'view');
        $this->createAttribute('root_product_model_edit_attribute', 'edit');
        $this->createAttribute('sub_product_model_axis_attribute', 'edit', false);
        $this->createAttribute('sub_product_model_no_view_attribute', 'none');
        $this->createAttribute('sub_product_model_view_attribute', 'view');
        $this->createAttribute('sub_product_model_edit_attribute', 'edit');
        $this->createAttribute('variant_product_axis_attribute', 'edit', false);
        $this->createAttribute('variant_product_no_view_attribute', 'none');
        $this->createAttribute('variant_product_view_attribute', 'view');
        $this->createAttribute('variant_product_edit_attribute', 'edit');
    }

    private function createFamilyVariant(): void
    {
        $family = $this->container->get('pim_catalog.factory.family')->create();
        $this->container->get('pim_catalog.updater.family')->update($family, [
            'code' => 'family_permission',
            'attributes'  => [
                'sku',
                'root_product_model_no_view_attribute',
                'root_product_model_view_attribute',
                'root_product_model_edit_attribute',
                'sub_product_model_axis_attribute',
                'sub_product_model_no_view_attribute',
                'sub_product_model_view_attribute',
                'sub_product_model_edit_attribute',
                'variant_product_axis_attribute',
                'variant_product_no_view_attribute',
                'variant_product_view_attribute',
                'variant_product_edit_attribute',
            ],
            'attribute_requirements' => [
                'tablet' => [
                    'sku',
                    'root_product_model_no_view_attribute',
                    'root_product_model_view_attribute',
                    'root_product_model_edit_attribute',
                    'sub_product_model_axis_attribute',
                    'sub_product_model_no_view_attribute',
                    'sub_product_model_view_attribute',
                    'sub_product_model_edit_attribute',
                    'variant_product_axis_attribute',
                    'variant_product_no_view_attribute',
                    'variant_product_view_attribute',
                    'variant_product_edit_attribute',
                ]
            ]
        ]);

        $errors = $this->container->get('validator')->validate($family);
        Assert::assertCount(0, $errors);
        $this->container->get('pim_catalog.saver.family')->save($family);

        $familyVariant = $this->container->get('pim_catalog.factory.family_variant')->create();
        $this->container->get('pim_catalog.updater.family_variant')->update($familyVariant, [
            'code' => 'family_variant_permission',
            'family' => 'family_permission',
            'labels' => [
                'en_US' => 'My family variant'
            ],
            'variant_attribute_sets' => [
                [
                    'axes' => ['sub_product_model_axis_attribute'],
                    'attributes' => [
                        'sub_product_model_axis_attribute',
                        'sub_product_model_view_attribute',
                        'sub_product_model_edit_attribute',
                        'sub_product_model_no_view_attribute'
                    ],
                    'level'=> 1,
                ],
                [
                    'axes' => ['variant_product_axis_attribute'],
                    'attributes' => [
                        'variant_product_axis_attribute',
                        'sku',
                        'variant_product_view_attribute',
                        'variant_product_edit_attribute',
                        'variant_product_no_view_attribute'
                    ],
                    'level'=> 2,
                ]
            ],
        ]);

        $errors = $this->container->get('validator')->validate($familyVariant);
        Assert::assertCount(0, $errors);
        $this->container->get('pim_catalog.saver.family_variant')->save($familyVariant);
    }

    /**
     * @param string $identifier
     * @param array  $data
     *
     * @return VariantProductInterface
     * @throws \Exception
     */
    protected function createVariantProduct($identifier, array $data = []) : VariantProductInterface
    {
        $product = $this->container->get('pim_catalog.builder.variant_product')->createProduct($identifier);
        $this->container->get('pim_catalog.updater.product')->update($product, $data);

        $errors = $this->container->get('pim_catalog.validator.product')->validate($product);

        Assert::assertCount(0, $errors);

        $this->container->get('pim_catalog.saver.product')->save($product);

        $this->container->get('akeneo_elasticsearch.client.product')->refreshIndex();

        return $product;
    }

    /**
     * @param string $identifier
     * @param array  $data
     *
     * @return ProductInterface
     * @throws \Exception
     */
    protected function createProduct($identifier, array $data = []) : ProductInterface
    {
        $product = $this->container->get('pim_catalog.builder.product')->createProduct($identifier);
        $this->container->get('pim_catalog.updater.product')->update($product, $data);

        $errors = $this->container->get('pim_catalog.validator.product')->validate($product);

        Assert::assertCount(0, $errors);

        $this->container->get('pim_catalog.saver.product')->save($product);

        $this->container->get('akeneo_elasticsearch.client.product')->refreshIndex();

        return $product;
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
        $this->container->get('akeneo_elasticsearch.client.product_model')->refreshIndex();

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

        $category = $entityManager->getRepository('PimCatalogBundle:Category')->findOneBy(['code' => $categoryCode]);
        $userGroup = $entityManager->getRepository('OroUserBundle:Group')->findOneBy(['name' => $userGroupName]);

        $accessManager->revokeAccess($category);
        $accessManager->grantAccess($category, $userGroup, $right);

        $entityManager->flush();
    }

    /**
     * @param string $categoryCode
     */
    private function revokeCategoryAccesses(string $categoryCode): void
    {
        $accessManager = $this->container->get('pimee_security.manager.category_access');
        $entityManager = $this->container->get('doctrine.orm.default_entity_manager');

        $category = $entityManager->getRepository('PimCatalogBundle:Category')->findOneBy(['code' => $categoryCode]);
        $accessManager->revokeAccess($category);
        $entityManager->flush();
    }

    /**
     * @param string $code
     * @param bool   $localizable
     * @param string $right
     */
    private function createAttribute(string $code, string $right, bool $localizable = true): void
    {
        switch ($right) {
            case 'view':
                $attributeGroup = 'attributeGroupB';
                break;
            case 'edit':
                $attributeGroup = 'attributeGroupA';
                break;
            default:
                $attributeGroup = 'attributeGroupC';
        }

        $data = [
            'code' => $code,
            'type' => AttributeTypes::BOOLEAN,
            'localizable' => $localizable,
            'scopable' => false,
            'group' => $attributeGroup
        ];

        $attribute = $this->container->get('pim_catalog.factory.attribute')->create();
        $this->container->get('pim_catalog.updater.attribute')->update($attribute, $data);
        $constraints = $this->container->get('validator')->validate($attribute);
        Assert::assertCount(0, $constraints);
        $this->container->get('pim_catalog.saver.attribute')->save($attribute);
    }
}
