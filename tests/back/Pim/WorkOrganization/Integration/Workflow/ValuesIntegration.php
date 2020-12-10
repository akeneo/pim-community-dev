<?php

namespace AkeneoTestEnterprise\Pim\WorkOrganization\Integration\Workflow;

use Akeneo\Pim\Enrichment\Component\Product\Exception\InvalidAttributeException;
use Akeneo\Pim\Enrichment\Component\Product\Exception\UnknownAttributeException;
use Akeneo\Pim\Permission\Component\Exception\ResourceAccessDeniedException;
use AkeneoTestEnterprise\Pim\Permission\Integration\Security\AbstractSecurityTestCase;
use Symfony\Component\Security\Core\Exception\InvalidArgumentException;

/**
 * +------------------------------------------+-----------------------+
 * |              Attributes                  |         Roles         |
 * |                                          |  Redactor |  Manager  |
 * +------------------------------------------+-----------------------+
 * | a_text                                   | View,Edit | View,Edit |
 * | a_number_float                           |    View   | View,Edit |
 * | a_localized_and_scopable_text_area-fr_FR |    View   | View,Edit |
 * | a_localized_and_scopable_text_area-en_US | View,Edit | View,Edit |
 * | a_localized_and_scopable_text_area-de_DE |     -     | View,Edit |
 * +------------------------------------------+-----------------------+
 *
 * +----------+--------------------------------------------------------------------------+
 * |          |                             Categories                                   |
 * +  Roles   +--------------------------------------------------------------------------+
 * |          |     master    |   categoryA   |  categoryA1 / categoryA2 |   categoryB   |
 * +----------+--------------------------------------------------------------------------+
 * | Redactor | View,Edit,Own |   View,Edit   |            View          |       -       |
 * | Manager  | View,Edit,Own | View,Edit,Own |       View,Edit,Own      | View,Edit,Own |
 * +----------+--------------------------------------------------------------------------+
 */
class ValuesIntegration extends AbstractSecurityTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $product = $this->saveProduct('product_a', [
            'categories' => ['categoryA'],
            'values' => [
                'a_text' => [
                    ['data' => 'text', 'locale' => null, 'scope' => null]
                ],
                'a_number_float' => [
                    ['data' => '15.1234567890', 'locale' => null, 'scope' => null]
                ],
                'a_localized_and_scopable_text_area' => [
                    ['data' => 'mon text', 'locale' => 'fr_FR', 'scope' => 'ecommerce'],
                    ['data' => 'mein text', 'locale' => 'de_DE', 'scope' => 'ecommerce'],
                ]
            ]
        ]);

        $this->get('akeneo_storage_utils.doctrine.object_detacher')->detach($product);
    }

    public function testAddNewAttributeOnProductDraft()
    {
        $updatedValues = ['a_date' => [['data' => '2017-03-03', 'locale' => null, 'scope' => null]]];
        $draftValues = ['a_date' => [['locale' => null, 'scope' => null, 'data' => '2017-03-03T00:00:00+01:00']]];

        $this->assert($updatedValues, $draftValues);
    }

    public function testDeleteAttributeOnProductDraft()
    {
        $updatedValues = ['a_text' => [['data' => null, 'locale' => null, 'scope' => null]]];
        $draftValues = ['a_text' => [['locale' => null, 'scope' => null, 'data' => null]]];

        $this->assert($updatedValues, $draftValues);
    }

    public function testAddNewLocalizableAttributeOnProductDraft()
    {
        $updatedValues = ['a_localized_and_scopable_text_area' => [['data' => 'my text', 'locale' => 'en_US', 'scope' => 'ecommerce']]];
        $draftValues = ['a_localized_and_scopable_text_area' => [['locale' => 'en_US', 'scope' => 'ecommerce', 'data' => 'my text']]];

        $this->assert($updatedValues, $draftValues);
    }

    public function testUpdateAProductDraftWithAttributeGroupNotViewable()
    {
        $this->expectException(UnknownAttributeException::class);
        $this->expectExceptionMessage('The a_multi_select attribute does not exist in your PIM.');

        $product = $this->saveProduct('product', ['categories' => ['categoryA'], 'values' => ['a_multi_select' => [['data' => ['optionB'], 'locale' => null, 'scope' => null]]]]);
        $this->generateToken('mary');

        $this->updateProduct($product, [
            'values' => [
                'a_multi_select' => [['data' => ['optionB'], 'locale' => null, 'scope' => null]]
            ]
        ]);
    }

    public function testUpdateAProductDraftWithAttributeGroupOnlyViewableWithChange()
    {
        $this->expectException(ResourceAccessDeniedException::class);
        $this->expectExceptionMessage('Attribute "a_number_float" belongs to the attribute group "attributeGroupB" on which you only have view permission.');

        $product = $this->saveProduct('product', ['categories' => ['categoryA'], 'values' => ['a_number_float' => [['data' => 12, 'locale' => null, 'scope' => null]]]]);
        $this->generateToken('mary');

        $this->updateProduct($product, [
            'values' => [
                'a_number_float' => [['data' => 14, 'locale' => null, 'scope' => null]]
            ]
        ]);
    }

    public function testUpdateAProductDraftWithAttributeGroupOnlyViewableWithoutChange()
    {
        $product = $this->saveProduct('product', ['categories' => ['categoryA'], 'values' => ['a_number_float' => [['data' => 12, 'locale' => null, 'scope' => null]]]]);
        $this->generateToken('mary');

        $this->updateProduct($product, [
            'values' => [
                'a_number_float' => [['data' => 12, 'locale' => null, 'scope' => null]]
            ]
        ]);

        $this->assertSame($product->getValue('a_number_float')->getData(), 12);
    }

    public function testUpdateAProductDraftWithLocaleNotViewable()
    {
        $this->expectException(InvalidAttributeException::class);
        $this->expectExceptionMessage('Attribute "a_localized_and_scopable_text_area" expects an existing and activated locale, "de_DE" given');

        $product = $this->saveProduct('product', ['categories' => ['categoryA'], 'values' => ['a_localized_and_scopable_text_area' => [['data' => 'text', 'locale' => 'de_DE', 'scope' => 'ecommerce']]]]);
        $this->generateToken('mary');

        $this->updateProduct($product, ['values' => ['a_localized_and_scopable_text_area' => [['data' => 'text', 'locale' => 'de_DE', 'scope' => 'ecommerce']]]]);
    }

    public function testUpdateAProductDraftWithLocaleOnlyViewableWithChange()
    {
        $this->expectException(ResourceAccessDeniedException::class);
        $this->expectExceptionMessage('You only have a view permission on the locale "fr_FR"');

        $product = $this->saveProduct('product', ['categories' => ['categoryA'], 'values' => ['a_localized_and_scopable_text_area' => [['data' => 'text', 'locale' => 'fr_FR', 'scope' => 'ecommerce']]]]);
        $this->generateToken('mary');

        $this->updateProduct($product, ['values' => ['a_localized_and_scopable_text_area' => [['data' => 'text FR', 'locale' => 'fr_FR', 'scope' => 'ecommerce']]]]);
    }

    public function testUpdateAProductDraftWithLocaleOnlyViewableWithoutChange()
    {
        $product = $this->saveProduct('product', ['categories' => ['categoryA'], 'values' => ['a_localized_and_scopable_text_area' => [['data' => 'text', 'locale' => 'fr_FR', 'scope' => 'ecommerce']]]]);
        $this->generateToken('mary');

        $this->updateProduct($product, ['values' => ['a_localized_and_scopable_text_area' => [['data' => 'text', 'locale' => 'fr_FR', 'scope' => 'ecommerce']]]]);
        $this->assertSame($product->getValue('a_localized_and_scopable_text_area', 'fr_FR', 'ecommerce')->getData(), 'text');
    }

    public function testUpdateEnabledFieldOnProductDraft()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('You cannot update the field "enabled". You should at least own this product to do it');

        $product = $this->saveProduct('product', ['categories' => ['categoryA'], 'enabled' => false]);
        $this->generateToken('mary');

        $this->updateProduct($product, ['enabled' => true]);
    }

    public function testUpdateEnabledFieldOnProductDraftWithoutChange()
    {
        $product = $this->saveProduct('product', ['categories' => ['categoryA'], 'enabled' => false]);
        $this->generateToken('mary');

        $this->updateProduct($product, ['enabled' => false]);
        $this->assertFalse($product->isEnabled());
    }

    public function testUpdateFamilyFieldOnProductDraft()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('You cannot update the field "family". You should at least own this product to do it');

        $product = $this->saveProduct('product', ['categories' => ['categoryA'], 'family' => 'familyA']);
        $this->generateToken('mary');

        $this->updateProduct($product, ['family' => 'familyB']);
    }

    public function testUpdateFamilyFieldOnProductDraftWithoutChange()
    {
        $product = $this->saveProduct('product', ['categories' => ['categoryA'], 'family' => 'familyA']);
        $this->generateToken('mary');

        $this->updateProduct($product, ['family' => 'familyA']);
        $this->assertSame('familyA', $product->getFamily()->getCode());
    }

    public function testUpdateGroupsFieldOnProductDraft()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('You cannot update the field "groups". You should at least own this product to do it');

        $product = $this->saveProduct('product', ['categories' => ['categoryA'], 'groups' => ['groupA']]);
        $this->generateToken('mary');

        $this->updateProduct($product, ['groups' => ['groupB']]);
    }

    public function testUpdateGroupsFieldOnProductDraftWithoutChange()
    {
        $product = $this->saveProduct('product', ['categories' => ['categoryA'], 'groups' => ['groupA']]);
        $this->generateToken('mary');

        $this->updateProduct($product, ['groups' => ['groupA']]);
        $this->assertSame('groupA', $product->getGroups()->first()->getCode());
    }

    public function testUpdateCategoriesFieldOnProductDraft()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('You cannot update the field "categories". You should at least own this product to do it');

        $product = $this->saveProduct('product', ['categories' => ['categoryA']]);
        $this->generateToken('mary');

        $this->updateProduct($product, ['categories' => 'categoriesA1']);
    }

    public function testUpdateCategoriesFieldOnProductDraftWithoutChange()
    {
        $product = $this->saveProduct('product', ['categories' => ['categoryA']]);
        $this->generateToken('mary');

        $this->updateProduct($product, ['categories' => ['categoryA']]);
        $this->assertSame('categoryA', $product->getCategories()->first()->getCode());
    }

    public function testUpdateAssociationsFieldOnProductDraft()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('You cannot update the field "associations". You should at least own this product to do it');

        $product = $this->saveProduct('product', ['categories' => ['categoryA'], 'associations' => [
            'X_SELL' => [
                'products' => ['product_a']
            ]
        ]]);
        $this->generateToken('mary');

        $this->updateProduct($product, [
            'associations' => [
                'X_SELL' => [
                    'products' => []
                ]
            ],
            'quantified_associations' => []
        ]);
    }

    public function testUpdateAssociationsFieldOnProductDraftWithoutChange()
    {
        $product = $this->saveProduct('product', ['categories' => ['categoryA'], 'associations' => [
            'X_SELL' => [
                'products' => ['product_a'],
                'groups'   => ['groupA', 'groupB']
            ]
        ]]);
        $this->generateToken('mary');

        $this->updateProduct($product, [
            'associations' => [
                'X_SELL' => [
                    'products' => ['product_a']
                ]
            ],
            'quantified_associations' => []
        ]);
        $associatedProducts = $product->getAssociatedProducts('X_SELL');
        $this->assertSame('product_a', $associatedProducts ? $associatedProducts->first()->getIdentifier() : null);

        $this->updateProduct($product, ['associations' => ['X_SELL' => ['products' => ['product_a'], 'groups' => ['groupB', 'groupA']]]]);

        $associatedProductsAfterUpdate = $product->getAssociatedProducts('X_SELL');
        $this->assertSame('product_a', $associatedProductsAfterUpdate ? $associatedProductsAfterUpdate->first()->getIdentifier() : null);
    }

    public function testUpdateMultipleFieldOnProductDraft()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('You cannot update the following fields "categories, enabled". You should at least own this product to do it');

        $product = $this->saveProduct('product', ['categories' => ['categoryA'], 'enabled' => true]);
        $this->generateToken('mary');

        $this->updateProduct($product, ['categories' => 'categoriesA1', 'enabled' => false]);
    }

    /**
     * @param array $updatedValues
     * @param array $draftValues
     */
    private function assert(array $updatedValues, array $draftValues): void
    {
        $this->generateToken('mary');
        $product = $this->getProduct('product_a');

        $this->updateProduct($product, ['values' => $updatedValues]);
        $this->get('pimee_workflow.saver.product_delegating')->save($product);

        $this->assertSame($product->getRawValues(), [
            'sku' => [
                '<all_channels>' => [
                    '<all_locales>' => 'product_a'
                ]
            ],
            'a_text' => [
                '<all_channels>' => [
                    '<all_locales>' => 'text'
                ]
            ],
            'a_number_float' => [
                '<all_channels>' => [
                    '<all_locales>' => '15.123456789'
                ]
            ],
            'a_localized_and_scopable_text_area' => [
                'ecommerce' => [
                    'de_DE' => 'mein text',
                    'fr_FR' => 'mon text',
                ]
            ]
        ]);

        $draft = $this->get('pimee_workflow.repository.product_draft')->findUserEntityWithValuesDraft($product, 'mary');
        $this->assertNotNull($draft);
        $this->assertSame($draftValues, $draft->getChanges()['values']);
    }
}
