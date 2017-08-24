<?php

namespace PimEnterprise\Component\Catalog\tests\integration\Security\ProductDraft;

use PimEnterprise\Component\Catalog\tests\integration\Security\AbstractSecurityTestCase;

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
    protected function setUp()
    {
        parent::setUp();

        $product = $this->saveProduct('product_a', [
            'categories' => ['categoryA'],
            'values' => [
                'a_text' => [
                    ['data' => 'text', 'locale' => null, 'scope' => null]
                ],
                'a_number_float' => [
                    ['data' => 15, 'locale' => null, 'scope' => null]
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

    /**
     * @expectedException \Akeneo\Component\StorageUtils\Exception\UnknownPropertyException
     * @expectedExceptionMessage Property "a_multi_select" does not exist.
     */
    public function testUpdateAProductDraftWithAttributeGroupNotViewable()
    {
        $product = $this->saveProduct('product', ['categories' => ['categoryA'], 'values' => ['a_multi_select' => [['data' => ['optionB'], 'locale' => null, 'scope' => null]]]]);
        $this->generateToken('mary');

        $this->updateProduct($product, [
            'values' => [
                'a_multi_select' => [['data' => ['optionB'], 'locale' => null, 'scope' => null]]
            ]
        ]);
    }

    /**
     * @expectedException \PimEnterprise\Component\Security\Exception\ResourceAccessDeniedException
     * @expectedExceptionMessage Attribute "a_number_float" belongs to the attribute group "attributeGroupB" on which you only have view permission.
     */
    public function testUpdateAProductDraftWithAttributeGroupOnlyViewableWithChange()
    {
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

    /**
     * @expectedException \Akeneo\Component\StorageUtils\Exception\UnknownPropertyException
     * @expectedExceptionMessage Attribute "a_localized_and_scopable_text_area" expects an existing and activated locale, "de_DE" given
     */
    public function testUpdateAProductDraftWithLocaleNotViewable()
    {
        $product = $this->saveProduct('product', ['categories' => ['categoryA'], 'values' => ['a_localized_and_scopable_text_area' => [['data' => 'text', 'locale' => 'de_DE', 'scope' => 'ecommerce']]]]);
        $this->generateToken('mary');

        $this->updateProduct($product, ['values' => ['a_localized_and_scopable_text_area' => [['data' => 'text', 'locale' => 'de_DE', 'scope' => 'ecommerce']]]]);
    }

    /**
     * @expectedException \PimEnterprise\Component\Security\Exception\ResourceAccessDeniedException
     * @expectedExceptionMessage You only have a view permission on the locale "fr_FR"
     */
    public function testUpdateAProductDraftWithLocaleOnlyViewableWithChange()
    {
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

    /**
     * @expectedException \Symfony\Component\Security\Core\Exception\InvalidArgumentException
     * @expectedExceptionMessage You cannot update the field "enabled". You should at least own this product to do it
     */
    public function testUpdateEnabledFieldOnProductDraft()
    {
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

    /**
     * @expectedException \Symfony\Component\Security\Core\Exception\InvalidArgumentException
     * @expectedExceptionMessage You cannot update the field "family". You should at least own this product to do it
     */
    public function testUpdateFamilyFieldOnProductDraft()
    {
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

    /**
     * @expectedException \Symfony\Component\Security\Core\Exception\InvalidArgumentException
     * @expectedExceptionMessage You cannot update the field "groups". You should at least own this product to do it
     */
    public function testUpdateGroupsFieldOnProductDraft()
    {
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

    /**
     * @expectedException \Symfony\Component\Security\Core\Exception\InvalidArgumentException
     * @expectedExceptionMessage You cannot update the field "categories". You should at least own this product to do it
     */
    public function testUpdateCategoriesFieldOnProductDraft()
    {
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

    /**
     * @expectedException \Symfony\Component\Security\Core\Exception\InvalidArgumentException
     * @expectedExceptionMessage You cannot update the field "associations". You should at least own this product to do it
     */
    public function testUpdateAssociationsFieldOnProductDraft()
    {
        $product = $this->saveProduct('product', ['categories' => ['categoryA'], 'associations' => [
            'X_SELL' => [
                'products' => ['product_a']
            ]
        ]]);
        $this->generateToken('mary');

        $this->updateProduct($product, ['associations' => [
            'X_SELL' => [
                'products' => []
            ]
        ]]);
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

        $this->updateProduct($product, ['associations' => [
            'X_SELL' => [
                'products' => ['product_a'],
            ]
        ]]);
        $this->assertSame('product_a', $product->getAssociationForTypeCode('X_SELL')->getProducts()->first()->getIdentifier());

        $this->updateProduct($product, ['associations' => ['X_SELL' => ['products' => ['product_a'], 'groups' => ['groupB', 'groupA']]]]);
        $this->assertSame('product_a', $product->getAssociationForTypeCode('X_SELL')->getProducts()->first()->getIdentifier());
    }

    /**
     * @expectedException \Symfony\Component\Security\Core\Exception\InvalidArgumentException
     * @expectedExceptionMessage You cannot update the following fields "categories, enabled". You should at least own this product to do it
     */
    public function testUpdateMultipleFieldOnProductDraft()
    {
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
                    '<all_locales>' => '15.0000'
                ]
            ],
            'a_localized_and_scopable_text_area' => [
                'ecommerce' => [
                    'de_DE' => 'mein text',
                    'fr_FR' => 'mon text',
                ]
            ]
        ]);

        $draft = $this->get('pimee_workflow.repository.product_draft')->findUserProductDraft($product, 'mary');
        $this->assertSame($draftValues, $draft->getChanges()['values']);
    }
}
