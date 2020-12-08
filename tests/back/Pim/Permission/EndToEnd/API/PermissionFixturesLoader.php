<?php

declare(strict_types=1);

namespace AkeneoTestEnterprise\Pim\Permission\EndToEnd\API;

use Akeneo\Pim\Enrichment\Component\Category\Model\Category;
use Akeneo\Pim\Enrichment\Component\Product\Builder\ProductBuilderInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Permission\Bundle\Manager\CategoryAccessManager;
use Akeneo\Pim\Permission\Component\Attributes;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;
use Akeneo\Tool\Component\Classification\Model\CategoryInterface;
use Akeneo\Tool\Component\StorageUtils\Factory\SimpleFactoryInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Akeneo\UserManagement\Component\Model\Group;
use Doctrine\Common\Persistence\ObjectManager;
use PHPUnit\Framework\Assert;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Validator\ValidatorBuilderInterface;

/**
 * @author    Alexandre Hocquard <alexandre.hocquard@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class PermissionFixturesLoader
{
    /** @var SimpleFactoryInterface */
    private $attributeFactory;

    /** @var ObjectUpdaterInterface */
    private $attributeUpdater;

    /** @var SaverInterface */
    private $attributeSaver;

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

    /** @var ProductBuilderInterface */
    private $productBuilder;

    /** @var ObjectUpdaterInterface */
    private $productUpdater;

    /** @var ValidatorBuilderInterface */
    private $productValidator;

    /** @var SaverInterface */
    private $productSaver;

    /** @var SimpleFactoryInterface */
    private $productModelFactory;

    /** @var ObjectUpdaterInterface */
    private $productModelUpdater;

    /** @var SaverInterface */
    private $productModelSaver;

    /** @var SimpleFactoryInterface */
    private $categoryFactory;

    /** @var ObjectUpdaterInterface */
    private $categoryUpdater;

    /** @var SaverInterface */
    private $categorySaver;

    /** @var CategoryAccessManager */
    private $categoryAccessManager;

    /** @var ObjectManager */
    private $objectManager;

    /** @var Client */
    private $esClient;

    /** @var ValidatorInterface */
    private $validator;

    public function __construct(
        SimpleFactoryInterface $attributeFactory,
        ObjectUpdaterInterface $attributeUpdater,
        SaverInterface $attributeSaver,
        SimpleFactoryInterface $familyFactory,
        ObjectUpdaterInterface $familyUpdater,
        SaverInterface $familySaver,
        SimpleFactoryInterface $familyVariantFactory,
        ObjectUpdaterInterface $familyVariantUpdater,
        SaverInterface $familyVariantSaver,
        ProductBuilderInterface $productBuilder,
        ObjectUpdaterInterface $productUpdater,
        ValidatorInterface $productValidator,
        SaverInterface $productSaver,
        SimpleFactoryInterface $productModelFactory,
        ObjectUpdaterInterface $productModelUpdater,
        SaverInterface $productModelSaver,
        SimpleFactoryInterface $categoryFactory,
        ObjectUpdaterInterface $categoryUpdater,
        SaverInterface $categorySaver,
        CategoryAccessManager $categoryAccessManager,
        ObjectManager $objectManager,
        Client $esClient,
        ValidatorInterface $validator
    ) {
        $this->attributeFactory = $attributeFactory;
        $this->attributeUpdater = $attributeUpdater;
        $this->attributeSaver = $attributeSaver;
        $this->familyFactory = $familyFactory;
        $this->familyUpdater = $familyUpdater;
        $this->familySaver = $familySaver;
        $this->familyVariantFactory = $familyVariantFactory;
        $this->familyVariantUpdater = $familyVariantUpdater;
        $this->familyVariantSaver = $familyVariantSaver;
        $this->productBuilder = $productBuilder;
        $this->productUpdater = $productUpdater;
        $this->productValidator = $productValidator;
        $this->productSaver = $productSaver;
        $this->productModelFactory = $productModelFactory;
        $this->productModelUpdater = $productModelUpdater;
        $this->productModelSaver = $productModelSaver;
        $this->categoryFactory = $categoryFactory;
        $this->categoryUpdater = $categoryUpdater;
        $this->categorySaver = $categorySaver;
        $this->categoryAccessManager = $categoryAccessManager;
        $this->objectManager = $objectManager;
        $this->esClient = $esClient;
        $this->validator = $validator;
    }

    /**
     * For redactor user:
     * +----------+--------------------------------------------+
     * |                     Products                          |
     * +----------+--------------------------------------------+
     * | No view  | product_not_viewable                       |
     * +----------+--------------------------------------------+
     * | View     | product_with_one_viewable_category_        |
     *              and_another_not_viewable                   |
     * +----------+--------------------------------------------+
     * | Own      |                                            |
     * +----------+--------------------------------------------+
     * +----------+--------------------------------------------+
     * |                     Variant products                  |
     * +----------+--------------------------------------------+
     * | No view  | product_model_not_viewable                 |
     * +----------+--------------------------------------------+
     * | View     | product_model_with_one_viewable_category_  |
     *              and_another_not_viewable                   |
     * +----------+--------------------------------------------+
     * | Own      |                                            |
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
    public function loadProductsAndProductModelsForRemovedEvents(): void
    {
        $this->createCategoryFixtures();
        $this->createAttributeFixtures();
        $this->createFamilyVariant();

        $this->createProduct(
            'product_with_one_viewable_category_and_another_not_viewable',
            [
                'categories' => ['view_category', 'category_without_right'],
                'family' => 'familyA',
            ]
        );

        $this->createProduct(
            'product_not_viewable',
            [
                'categories' => ['category_without_right'],
                'family' => 'familyA',
            ]
        );

        $this->createProductModel(
            [
                'code' => 'product_model_not_viewable',
                'family_variant' => 'family_variant_permission',
                'categories' => ['category_without_right'],
            ]
        );

        $this->createProductModel(
            [
                'code' => 'product_model_with_one_viewable_category_and_another_not_viewable',
                'family_variant' => 'family_variant_permission',
                'categories' => ['view_category', 'category_without_right'],
            ]
        );
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
    public function loadProductsForAssociationPermissions(): void
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
            ],
        ];

        $subProductModel = [
            'code' => 'sub_product_model',
            'parent' => 'root_product_model',
            'values' => [
                'sub_product_model_axis_attribute' => [
                    ['locale' => null, 'scope' => null, 'data' => true],
                ],
            ],
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
                    'products' => ['product_no_view', 'product_view'],
                ],
            ],
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

        $productModelNoView = [
            'code' => 'product_model_no_view',
            'family_variant' => 'family_variant_permission',
            'categories' => ['category_without_right'],
        ];

        $productModelView = [
            'code' => 'product_model_view',
            'family_variant' => 'family_variant_permission',
            'categories' => ['view_category'],
        ];

        $this->createProductModel($rootProductModel);
        $this->createProductModel($subProductModel);
        $this->createProductModel($productModelView);
        $this->createProductModel($productModelNoView);
        $this->createProduct('product_no_view', $productNoView);
        $this->createProduct('product_view', $productView);
        $this->createProduct('product_own', $productOwn);
        $this->createProduct('variant_product', $variantProduct);
    }

    public function loadProductsForQuantifiedAssociationPermissions(): void
    {
        $this->createCategoryFixtures();
        $this->createAttributeFixtures();
        $this->createFamilyVariant();

        $this->createProduct(
            'product_viewable_by_everybody',
            [
                'categories' => ['categoryA2'],
            ]
        );

        $this->createProduct(
            'product_viewable_by_everybody_1',
            [
                'categories' => ['categoryA2'],
            ]
        );

        $this->createProduct(
            'product_not_viewable_by_redactor',
            [
                'categories' => ['categoryB'],
            ]
        );

        $this->createProduct(
            'product_without_category',
            [
                'categories' => [],
            ]
        );

        $this->createProductModel(
            [
                'code' => 'product_model_viewable_by_everybody',
                'family_variant' => 'family_variant_permission',
                'categories' => ['categoryA2', 'categoryB'],
            ]
        );

        $this->createProductModel(
            [
                'code' => 'product_model_viewable_by_everybody_1',
                'family_variant' => 'family_variant_permission',
                'categories' => ['categoryA2'],
            ]
        );

        $this->createProductModel(
            [
                'code' => 'product_model_not_viewable_by_redactor',
                'family_variant' => 'family_variant_permission',
                'categories' => ['categoryB'],
            ]
        );

        $this->createProductModel(
            [
                'code' => 'product_model_without_category',
                'family_variant' => 'family_variant_permission',
                'categories' => [],
            ]
        );

        $this->createProduct(
            'product_associated_with_product_and_product_model',
            [
                'categories' => ['categoryA2'],
                'quantified_associations' => [
                    'PRODUCTSET' => [
                        'products' => [
                            ['identifier' => 'product_not_viewable_by_redactor', 'quantity' => 1],
                            ['identifier' => 'product_viewable_by_everybody', 'quantity' => 2],
                            ['identifier' => 'product_without_category', 'quantity' => 3],
                        ],
                        'product_models' => [
                            ['identifier' => 'product_model_not_viewable_by_redactor', 'quantity' => 4],
                            ['identifier' => 'product_model_viewable_by_everybody', 'quantity' => 5],
                            ['identifier' => 'product_model_without_category', 'quantity' => 6],
                        ],
                    ],
                ],
            ]
        );

        $this->createProductModel(
            [
                'code' => 'product_model_associated_with_product_and_product_model',
                'family_variant' => 'family_variant_permission',
                'categories' => ['categoryA2'],
                'quantified_associations' => [
                    'PRODUCTSET' => [
                        'products' => [
                            ['identifier' => 'product_not_viewable_by_redactor', 'quantity' => 1],
                            ['identifier' => 'product_viewable_by_everybody', 'quantity' => 2],
                            ['identifier' => 'product_without_category', 'quantity' => 3],
                        ],
                        'product_models' => [
                            ['identifier' => 'product_model_not_viewable_by_redactor', 'quantity' => 4],
                            ['identifier' => 'product_model_viewable_by_everybody', 'quantity' => 5],
                            ['identifier' => 'product_model_without_category', 'quantity' => 6],
                        ],
                    ],
                ],
            ]
        );

        $this->createProduct(
            'product_owned_by_redactor_and_associated_with_product_and_product_model',
            [
                'categories' => ['own_category'],
                'quantified_associations' => [
                    'PRODUCTSET' => [
                        'products' => [
                            ['identifier' => 'product_not_viewable_by_redactor', 'quantity' => 1],
                            ['identifier' => 'product_viewable_by_everybody', 'quantity' => 2],
                            ['identifier' => 'product_without_category', 'quantity' => 3],
                        ],
                        'product_models' => [
                            ['identifier' => 'product_model_not_viewable_by_redactor', 'quantity' => 4],
                            ['identifier' => 'product_model_viewable_by_everybody', 'quantity' => 5],
                            ['identifier' => 'product_model_without_category', 'quantity' => 6],
                        ],
                    ],
                ],
            ]
        );

        $this->createProductModel(
            [
                'code' => 'product_model_owned_by_redactor_and_associated_with_product_and_product_model',
                'family_variant' => 'family_variant_permission',
                'categories' => ['own_category'],
                'quantified_associations' => [
                    'PRODUCTSET' => [
                        'products' => [
                            ['identifier' => 'product_not_viewable_by_redactor', 'quantity' => 1],
                            ['identifier' => 'product_viewable_by_everybody', 'quantity' => 2],
                            ['identifier' => 'product_without_category', 'quantity' => 3],
                        ],
                        'product_models' => [
                            ['identifier' => 'product_model_not_viewable_by_redactor', 'quantity' => 4],
                            ['identifier' => 'product_model_viewable_by_everybody', 'quantity' => 5],
                            ['identifier' => 'product_model_without_category', 'quantity' => 6],
                        ],
                    ],
                ],
            ]
        );
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
                    ['locale' => 'de_DE', 'scope' => null, 'data' => true],
                ],
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
            ],
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
            ],
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
            ],
        ];

        $this->createProductModel($rootProductModel);
        $this->createProductModel($subProductModel);
        $this->createProduct('variant_product', $variantProduct);
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
            ['code' => 'trousers', 'categories' => []],
        ];

        $subProductModels = [
            ['code' => 'colored_sweat_no_view', 'categories' => [], 'parent' => 'sweat_no_view'],
            ['code' => 'colored_shoes_view', 'categories' => ['category_without_right'], 'parent' => 'shoes_view'],
            ['code' => 'colored_tshirt_view', 'categories' => ['view_category'], 'parent' => 'tshirt_view'],
            ['code' => 'colored_sweat_edit', 'categories' => ['category_without_right'], 'parent' => 'sweat_edit'],
            ['code' => 'colored_shoes_edit', 'categories' => ['edit_category'], 'parent' => 'shoes_no_view'],
            ['code' => 'colored_jacket_own', 'categories' => ['own_category'], 'parent' => 'jacket_no_view'],
            ['code' => 'colored_shoes_own', 'categories' => ['category_without_right'], 'parent' => 'shoes_own'],
            ['code' => 'colored_trousers', 'parent' => 'trousers'],
        ];

        $variantProducts = [
            'colored_sized_sweat_no_view' => [
                'categories' => ['category_without_right'],
                'parent' => 'colored_sweat_no_view',
            ],
            'colored_sized_shoes_view' => [
                'categories' => ['category_without_right'],
                'parent' => 'colored_shoes_view',
            ],
            'colored_sized_tshirt_view' => ['categories' => ['view_category'], 'parent' => 'colored_tshirt_view'],
            'colored_sized_sweat_edit' => [
                'categories' => ['category_without_right'],
                'parent' => 'colored_sweat_edit',
            ],
            'colored_sized_shoes_edit' => ['categories' => ['edit_category'], 'parent' => 'colored_shoes_edit'],
            'colored_sized_sweat_own' => ['categories' => [], 'parent' => 'colored_jacket_own'],
            'colored_sized_shoes_own' => ['categories' => ['own_category'], 'parent' => 'colored_shoes_own'],
            'colored_sized_trousers' => ['parent' => 'colored_trousers'],
        ];

        foreach ($rootProductModels as $rootProductModel) {
            $rootProductModel += [
                'family_variant' => 'familyVariantA1',
                'values' => [
                    'a_price' => [
                        'data' => [
                            'data' => [['amount' => '50', 'currency' => 'EUR']],
                            'locale' => null,
                            'scope' => null,
                        ],
                    ],
                    'a_number_float' => [['data' => '12.5', 'locale' => null, 'scope' => null]],
                    'a_localized_and_scopable_text_area' => [
                        [
                            'data' => 'my pink tshirt',
                            'locale' => 'en_US',
                            'scope' => 'ecommerce',
                        ],
                    ],
                ],
            ];

            $this->createProductModel($rootProductModel);
        }

        foreach ($subProductModels as $subProductModel) {
            $subProductModel += [
                'family_variant' => 'familyVariantA1',
                'values' => [
                    'a_simple_select' => [
                        ['locale' => null, 'scope' => null, 'data' => 'optionB'],
                    ],
                ],
            ];

            $this->createProductModel($subProductModel);
        }

        foreach ($variantProducts as $identifier => $data) {
            $data += [
                'values' => [
                    'a_yes_no' => [
                        ['locale' => null, 'scope' => null, 'data' => false],
                    ],
                ],
            ];

            $this->createProduct($identifier, $data);
        }
    }

    /**
     * @param string $code
     * @param string $right
     * @param bool $localizable
     * @param string $type
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function createAttribute(
        string $code,
        string $right,
        bool $localizable = true,
        $type = AttributeTypes::BOOLEAN
    ): void {
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
            'type' => $type,
            'localizable' => $localizable,
            'scopable' => false,
            'group' => $attributeGroup,
        ];

        $attribute = $this->attributeFactory->create();
        $this->attributeUpdater->update($attribute, $data);
        $constraints = $this->validator->validate($attribute);
        Assert::assertCount(0, $constraints);
        $this->attributeSaver->save($attribute);
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
        $family = $this->familyFactory->create();
        $this->familyUpdater->update(
            $family,
            [
                'code' => 'family_permission',
                'attributes' => [
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
                    ],
                ],
            ]
        );

        $errors = $this->validator->validate($family);
        Assert::assertCount(0, $errors);
        $this->familySaver->save($family);

        $familyVariant = $this->familyVariantFactory->create();
        $this->familyVariantUpdater->update(
            $familyVariant,
            [
                'code' => 'family_variant_permission',
                'family' => 'family_permission',
                'labels' => [
                    'en_US' => 'My family variant',
                ],
                'variant_attribute_sets' => [
                    [
                        'axes' => ['sub_product_model_axis_attribute'],
                        'attributes' => [
                            'sub_product_model_axis_attribute',
                            'sub_product_model_view_attribute',
                            'sub_product_model_edit_attribute',
                            'sub_product_model_no_view_attribute',
                        ],
                        'level' => 1,
                    ],
                    [
                        'axes' => ['variant_product_axis_attribute'],
                        'attributes' => [
                            'variant_product_axis_attribute',
                            'sku',
                            'variant_product_view_attribute',
                            'variant_product_edit_attribute',
                            'variant_product_no_view_attribute',
                        ],
                        'level' => 2,
                    ],
                ],
            ]
        );

        $errors = $this->validator->validate($familyVariant);
        Assert::assertCount(0, $errors);
        $this->familyVariantSaver->save($familyVariant);
    }

    /**
     * @param string $identifier
     * @param array $data
     *
     * @return ProductInterface
     * @throws \Exception
     */
    protected function createProduct($identifier, array $data = []): ProductInterface
    {
        $product = $this->productBuilder->createProduct($identifier);
        $this->productUpdater->update($product, $data);

        $errors = $this->productValidator->validate($product);

        Assert::assertCount(0, $errors);

        $this->productSaver->save($product);

        $this->esClient->refreshIndex();

        return $product;
    }

    /**
     * @param array $data
     *
     * @return ProductModelInterface
     * @throws \Exception
     */
    private function createProductModel(array $data = []): ProductModelInterface
    {
        $productModel = $this->productModelFactory->create();
        $this->productModelUpdater->update($productModel, $data);

        $errors = $this->productValidator->validate($productModel);

        Assert::assertCount(0, $errors);

        $this->productModelSaver->save($productModel);
        $this->esClient->refreshIndex();

        return $productModel;
    }

    /**
     * @param array $data
     *
     * @return CategoryInterface
     */
    private function createCategory(array $data = []): CategoryInterface
    {
        $category = $this->categoryFactory->create();
        $this->categoryUpdater->update($category, $data);
        $errors = $this->validator->validate($category);

        Assert::assertCount(0, $errors);

        $this->categorySaver->save($category);

        return $category;
    }

    /**
     * @param string $categoryCode
     * @param string $right
     * @param string $userGroupName
     */
    private function createCategoryAccesses(string $categoryCode, string $right, string $userGroupName): void
    {
        $category = $this->objectManager->getRepository(Category::class)->findOneBy(['code' => $categoryCode]);
        $userGroup = $this->objectManager->getRepository(Group::class)->findOneBy(['name' => $userGroupName]);

        $this->categoryAccessManager->revokeAccess($category);
        $this->objectManager->flush();
        $this->categoryAccessManager->grantAccess($category, $userGroup, $right);
    }

    /**
     * @param string $categoryCode
     */
    private function revokeCategoryAccesses(string $categoryCode): void
    {
        $category = $this->objectManager->getRepository(Category::class)->findOneBy(['code' => $categoryCode]);
        $this->categoryAccessManager->revokeAccess($category);
        $this->objectManager->flush();
    }
}
