<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2020 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace AkeneoTestEnterprise\Pim\Permission\Integration\Enrichment\EntityWithFamilyVariant;

use Akeneo\Pim\Enrichment\Component\Product\Model\GroupInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Enrichment\Component\Product\Value\OptionsValue;
use Akeneo\Pim\Enrichment\Component\Product\Value\OptionValue;
use Akeneo\Pim\Enrichment\Component\Product\Value\ScalarValue;
use Akeneo\Pim\Permission\Component\Exception\ResourceAccessDeniedException;
use Akeneo\Pim\Structure\Component\Model\AssociationType;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use PHPUnit\Framework\Assert;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

class ConvertVariantProductIntoSimpleProductIntegration extends TestCase
{
    /**
     * @test
     */
    public function it_throws_an_exception_if_the_user_does_not_own_the_product()
    {
        $nonGrantedProduct = $this->get('pim_catalog.repository.product')->findOneByIdentifier('non_viewable_product');
        $this->loginAsMary();

        $this->expectException(ResourceAccessDeniedException::class);
        $this->convertToSimpleProduct($nonGrantedProduct);
    }

    /**
     * @test
     */
    public function it_keeps_the_ancestor_values_even_if_they_are_not_viewable()
    {
        $this->loginAsMary();
        $product = $this->get('pim_catalog.repository.product')->findOneByIdentifier('owned_product');
        // mary can't view values for 'a_multi_select' nor the 'de_DE' 'locale
        Assert::assertNull($product->getValue('a_multi_select'));
        Assert::assertNull($product->getValue('a_multi_select', 'de_DE', 'tablet'));

        $this->convertToSimpleProduct($product);
        Assert::assertFalse($product->isVariant());

        $fullProduct = $this->get('pim_catalog.repository.product_without_permission')
                            ->findOneByIdentifier('owned_product');
        Assert::assertFalse($fullProduct->isVariant());

        $expectedValues = [
            ScalarValue::value('sku', 'owned_product'),
            ScalarValue::value('a_yes_no', true),
            OptionValue::value('a_simple_select', 'optionB'),
            OptionsValue::value('a_multi_select', ['optionA', 'optionB']),
            ScalarValue::scopableLocalizableValue(
                'a_localized_and_scopable_text_area',
                'English test',
                'tablet',
                'en_US'
            ),
            ScalarValue::scopableLocalizableValue(
                'a_localized_and_scopable_text_area',
                'German test',
                'tablet',
                'de_DE'
            ),
            ScalarValue::value('a_text', 'lorem ipsum dolor sit amet'),
        ];

        foreach ($expectedValues as $expectedValue) {
            $actualValue = $fullProduct->getValues()->getSame($expectedValue);
            Assert::assertEquals($expectedValue, $actualValue);
        }
    }

    /**
     * @test
     */
    public function it_keeps_the_ancestor_categories_even_if_they_are_not_viewable()
    {
        $this->loginAsMary();
        $product = $this->get('pim_catalog.repository.product')->findOneByIdentifier('owned_product');
        // mary can't view the "categoryC" category
        Assert::assertNotContains('categoryC', $product->getCategoryCodes());

        $this->convertToSimpleProduct($product);
        Assert::assertFalse($product->isVariant());

        $fullProduct = $this->get('pim_catalog.repository.product_without_permission')
                            ->findOneByIdentifier('owned_product');
        Assert::assertFalse($fullProduct->isVariant());
        Assert::assertEqualsCanonicalizing(['master', 'categoryC', 'categoryA'], $fullProduct->getCategoryCodes());
    }

    /**
     * @test
     */
    public function it_keeps_the_associated_products_and_models_even_if_they_are_not_viewable()
    {
        $this->loginAsMary();
        $product = $this->get('pim_catalog.repository.product')->findOneByIdentifier('owned_product');

        $this->convertToSimpleProduct($product);
        Assert::assertFalse($product->isVariant());

        /** @var ProductInterface $fullProduct */
        $fullProduct = $this->get('pim_catalog.repository.product_without_permission')
                            ->findOneByIdentifier('owned_product');
        $xsellAssociation = $fullProduct->getAssociationForTypeCode('X_SELL');
        Assert::assertEqualsCanonicalizing(
            ['empty_product', 'non_viewable_product', 'other_non_viewable_product'],
            $xsellAssociation->getProducts()->map(
                function (ProductInterface $associatedProduct): string {
                    return $associatedProduct->getIdentifier();
                }
            )->toArray(),
        );
        Assert::assertEqualsCanonicalizing(
            ['non_viewable_pm'],
            $xsellAssociation->getProductModels()->map(
                function (ProductModelInterface $associatedProductModel): string {
                    return $associatedProductModel->getCode();
                }
            )->toArray(),
        );
        Assert::assertEqualsCanonicalizing(
            ['groupA', 'groupB'],
            $xsellAssociation->getGroups()->map(
                function (GroupInterface $associatedGroup): string {
                    return $associatedGroup->getCode();
                }
            )->toArray(),
        );

        $expectedQuantifiedAssociation = [
            'QUANTIFIED' => [
                'products' => [
                    [
                        'identifier' => 'empty_product',
                        'quantity' => 7,
                    ],
                    [
                        'identifier' => 'non_viewable_product',
                        'quantity' => 5,
                    ],
                ],
                'product_models' => [
                    [
                        'identifier' => 'non_viewable_pm',
                        'quantity' => 2,
                    ],
                ],
            ],
        ];
        Assert::assertEqualsCanonicalizing(
            $expectedQuantifiedAssociation,
            $fullProduct->getQuantifiedAssociations()->normalize()
        );
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useTechnicalCatalog();
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->loadFixtures();
        $this->get('pim_catalog.validator.unique_value_set')->reset();
    }

    private function loginAsMary(): void
    {
        $user = $this->get('pim_user.repository.user')->findOneByIdentifier('mary');
        $this->get('security.token_storage')->setToken(
            new UsernamePasswordToken($user, null, 'main', $user->getRoles())
        );
    }

    /**
     * Mary:
     *  - has own privilege on master category (it's actually a root category, so normally there shouldn't be any
     *    product linked to it, but in this catalog it's the only category Mary owns)
     *  - has edit privileges on categoryA
     *  - cannot view products in categoryB and categoryC
     *  - cannot view attributes from attributeGroupC ("a_multi_select")
     *  - cannot view values with the de_DE locale
     */
    private function loadFixtures(): void
    {
        $associationType = new AssociationType();
        $associationType->setCode('QUANTIFIED');
        $associationType->setIsQuantified(true);
        $this->get('pim_catalog.saver.association_type')->save($associationType);

        $this->createProduct('empty_product', []);

        $this->createProductModel(
            [
                'code' => 'non_viewable_pm',
                'family_variant' => 'familyVariantA2',
                'categories' => ['categoryB'],
            ]
        );
        $this->createProduct(
            'non_viewable_product',
            [
                'parent' => 'non_viewable_pm',
                'categories' => ['categoryC'],
                'values' => [
                    'a_yes_no' => [['locale' => null, 'scope' => null, 'data' => true]],
                    'a_simple_select' => [['locale' => null, 'scope' => null, 'data' => 'optionA']],
                ],
            ]
        );

        $this->createProductModel(
            [
                'code' => 'other_non_viewable_pm',
                'family_variant' => 'familyVariantA2',
                'categories' => ['categoryC'],
                'values' => [
                    'a_localized_and_scopable_text_area' => [
                        [
                            'scope' => 'tablet',
                            'locale' => 'de_DE',
                            'data' => 'German test',
                        ],
                        [
                            'scope' => 'tablet',
                            'locale' => 'en_US',
                            'data' => 'English test',
                        ],
                    ],
                    'a_multi_select' => [['locale' => null, 'scope' => null, 'data' => ['optionA', 'optionB']]],
                ],
                'associations' => [
                    'X_SELL' => [
                        'products' => ['non_viewable_product'],
                        'product_models' => ['non_viewable_pm'],
                        'groups' => ['groupA'],
                    ],
                ],
                'quantified_associations' => [
                    'QUANTIFIED' => [
                        'product_models' => [
                            [
                                'identifier' => 'non_viewable_pm',
                                'quantity' => 2,
                            ],
                        ],
                        'products' => [
                            [
                                'identifier' => 'non_viewable_product',
                                'quantity' => 5,
                            ],
                        ],
                    ],
                ],
            ]
        );
        $this->createProduct(
            'other_non_viewable_product',
            [
                'parent' => 'other_non_viewable_pm',
                'categories' => ['categoryB'],
                'values' => [
                    'a_yes_no' => [['locale' => null, 'scope' => null, 'data' => false]],
                    'a_simple_select' => [['locale' => null, 'scope' => null, 'data' => 'optionB']],
                ],
            ]
        );
        $this->createProduct(
            'owned_product',
            [
                'parent' => 'other_non_viewable_pm',
                'categories' => ['categoryA', 'master'],
                'values' => [
                    'a_yes_no' => [['locale' => null, 'scope' => null, 'data' => true]],
                    'a_simple_select' => [['locale' => null, 'scope' => null, 'data' => 'optionB']],
                    'a_text' => [['locale' => null, 'scope' => null, 'data' => 'lorem ipsum dolor sit amet']],
                ],
                'associations' => [
                    'X_SELL' => [
                        'products' => ['empty_product', 'other_non_viewable_product'],
                        'groups' => ['groupB'],
                    ],
                ],
                'quantified_associations' => [
                    'QUANTIFIED' => [
                        'products' => [
                            [
                                'identifier' => 'empty_product',
                                'quantity' => 7,
                            ],
                        ],
                    ],
                ],
            ]
        );
    }

    private function createProduct(string $identifier, array $data): ProductInterface
    {
        $product = $this->get('pim_catalog.builder.product')->createProduct($identifier);
        $this->get('pim_catalog.updater.product')->update($product, $data);
        $this->saveProduct($product);

        return $product;
    }

    private function saveProduct(ProductInterface $product): void
    {
        $violations = $this->get('pim_catalog.validator.product')->validate($product);
        Assert::assertCount(0, $violations, sprintf('The product is not valid: %s', $violations));

        $this->get('pim_catalog.saver.product')->save($product);
    }

    private function createProductModel(array $data): void
    {
        $productModel = $this->get('pim_catalog.factory.product_model')->create();
        $this->get('pim_catalog.updater.product_model')->update($productModel, $data);

        $violations = $this->get('pim_catalog.validator.product_model')->validate($productModel);
        Assert::assertCount(0, $violations, sprintf('The product model is not valid: %s', $violations));

        $this->get('pim_catalog.saver.product_model')->save($productModel);
    }

    private function convertToSimpleProduct(ProductInterface $product): void
    {
        if (!$product->isVariant()) {
            throw new \InvalidArgumentException('The "%s" product is already simple', $product->getIdentifier());
        }
        $this->get('pim_catalog.entity_with_family_variant.remove_parent_from_product')->from(
            $product,
            ['parent' => null]
        );
        $this->saveProduct($product);
    }
}
