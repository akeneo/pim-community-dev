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
use Akeneo\Pim\Enrichment\Component\Product\Value\IdentifierValue;
use Akeneo\Pim\Enrichment\Component\Product\Value\OptionsValue;
use Akeneo\Pim\Enrichment\Component\Product\Value\OptionValue;
use Akeneo\Pim\Enrichment\Component\Product\Value\ScalarValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UpsertProductCommand;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\Association\AssociateGroups;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\Association\AssociateProducts;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\ChangeParent;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\ConvertToSimpleProduct;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\QuantifiedAssociation\AssociateQuantifiedProducts;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\QuantifiedAssociation\QuantifiedEntity;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetBooleanValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetCategories;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetSimpleSelectValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetTextValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\UserIntent;
use Akeneo\Pim\Enrichment\Product\API\ValueObject\ProductIdentifier;
use Akeneo\Pim\Permission\Component\Exception\ResourceViewAccessDeniedException;
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

        $this->expectException(ResourceViewAccessDeniedException::class);
        $this->convertToSimpleProduct($nonGrantedProduct, 'mary');
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

        $product = $this->convertToSimpleProduct($product, 'mary');
        Assert::assertFalse($product->isVariant());

        $fullProduct = $this->get('pim_catalog.repository.product_without_permission')
                            ->findOneByIdentifier('owned_product');
        Assert::assertFalse($fullProduct->isVariant());

        $expectedValues = [
            IdentifierValue::value('sku', true, 'owned_product'),
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

        $product = $this->convertToSimpleProduct($product, 'mary');
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

        $product = $this->convertToSimpleProduct($product, 'mary');
        Assert::assertFalse($product->isVariant());

        /** @var ProductInterface $fullProduct */
        $fullProduct = $this->get('pim_catalog.repository.product_without_permission')
                            ->findOneByIdentifier('owned_product');
        Assert::assertTrue($fullProduct->hasAssociationForTypeCode('X_SELL'));
        Assert::assertEqualsCanonicalizing(
            ['empty_product', 'non_viewable_product', 'other_non_viewable_product'],
            $fullProduct->getAssociatedProducts('X_SELL')->map(
                fn (ProductInterface $associatedProduct): string => $associatedProduct->getIdentifier()
            )->toArray()
        );
        Assert::assertEqualsCanonicalizing(
            ['non_viewable_pm'],
            $fullProduct->getAssociatedProductModels('X_SELL')->map(
                fn (ProductModelInterface $associatedProductModel): string => $associatedProductModel->getCode()
            )->toArray()
        );
        Assert::assertEqualsCanonicalizing(
            ['groupA', 'groupB'],
            $fullProduct->getAssociatedGroups('X_SELL')->map(
                fn (GroupInterface $associatedGroup): string => $associatedGroup->getCode()
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
        return $this->catalog->useTechnicalCatalog(featureFlags: ['permission']);
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
            new UsernamePasswordToken($user, 'main', $user->getRoles())
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

        $this->createOrUpdateProduct('empty_product', []);

        $this->createProductModel(
            [
                'code' => 'non_viewable_pm',
                'family_variant' => 'familyVariantA2',
                'categories' => ['categoryB'],
            ]
        );
        $this->createOrUpdateProduct(
            'non_viewable_product',
            [
                new ChangeParent('non_viewable_pm'),
                new SetCategories(['categoryC']),
                new SetBooleanValue('a_yes_no', null, null, true),
                new SetSimpleSelectValue('a_simple_select', null, null, 'optionA'),
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
        $this->createOrUpdateProduct(
            'other_non_viewable_product',
            [
                new ChangeParent('other_non_viewable_pm'),
                new SetCategories(['categoryB']),
                new SetBooleanValue('a_yes_no', null, null, false),
                new SetSimpleSelectValue('a_simple_select', null, null, 'optionB'),
            ]
        );
        $this->createOrUpdateProduct(
            'owned_product',
            [
                new ChangeParent('other_non_viewable_pm'),
                new SetCategories(['categoryA', 'master']),
                new SetBooleanValue('a_yes_no', null, null, true),
                new SetSimpleSelectValue('a_simple_select', null, null, 'optionB'),
                new SetTextValue('a_text', null, null, 'lorem ipsum dolor sit amet'),
                new AssociateProducts('X_SELL', ['empty_product', 'other_non_viewable_product']),
                new AssociateGroups('X_SELL', ['groupB']),
                new AssociateQuantifiedProducts('QUANTIFIED', [new QuantifiedEntity('empty_product', 7)]),
            ]
        );
    }

    /**
     * @param UserIntent[] $userIntents
     */
    private function createOrUpdateProduct(string $identifier, array $userIntents, string $userName = 'admin'): ProductInterface
    {
        $this->get('akeneo_integration_tests.helper.authenticator')->logIn($userName);

        $this->get('pim_enrich.product.message_bus')->dispatch(
            UpsertProductCommand::createWithIdentifier($this->getUserId($userName), ProductIdentifier::fromIdentifier($identifier), $userIntents)
        );
        $this->get('akeneo_elasticsearch.client.product_and_product_model')->refreshIndex();

        return $this->get('pim_catalog.repository.product')->findOneByIdentifier($identifier);
    }

    private function createProductModel(array $data): void
    {
        $productModel = $this->get('pim_catalog.factory.product_model')->create();
        $this->get('pim_catalog.updater.product_model')->update($productModel, $data);

        $violations = $this->get('pim_catalog.validator.product_model')->validate($productModel);
        Assert::assertCount(0, $violations, sprintf('The product model is not valid: %s', $violations));

        $this->get('pim_catalog.saver.product_model')->save($productModel);
    }

    private function convertToSimpleProduct(ProductInterface $product, string $userName): ProductInterface
    {
        return $this->createOrUpdateProduct($product->getIdentifier(), [new ConvertToSimpleProduct()], $userName);
    }

    private function getUserId(string $username): int
    {
        $query = <<<SQL
            SELECT id FROM oro_user WHERE username = :username
        SQL;
        $stmt = $this->get('database_connection')->executeQuery($query, ['username' => $username]);
        $id = $stmt->fetchOne();
        Assert::assertNotNull($id);
        Assert::assertNotFalse($id);

        return \intval($id);
    }
}
