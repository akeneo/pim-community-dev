<?php

namespace AkeneoTestEnterprise\Pim\Permission\Integration\Security\Product;

use Akeneo\Pim\Permission\Component\Exception\ResourceAccessDeniedException;
use Akeneo\Tool\Component\StorageUtils\Exception\UnknownPropertyException;
use AkeneoTestEnterprise\Pim\Permission\Integration\Security\AbstractSecurityTestCase;

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
 */
class ValuesIntegration extends AbstractSecurityTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->saveProduct('product_a', [
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
    }

    public function testAddNewAttributeOnProduct()
    {
        $this->generateToken('mary');
        $product = $this->getProduct('product_a');

        $this->updateProduct($product, [
            'values' => [
                'a_date' => [
                    ['data' => '2017-03-03', 'locale' => null, 'scope' => null]
                ]
            ]
        ]);
        $this->get('pim_catalog.saver.product')->save($product);

        $expectedValues = '{"sku": {"<all_channels>": {"<all_locales>": "product_a"}}, ';
        $expectedValues.= '"a_date": {"<all_channels>": {"<all_locales>": "2017-03-03T00:00:00+01:00"}}, ';
        $expectedValues.= '"a_text": {"<all_channels>": {"<all_locales>": "text"}}, ';
        $expectedValues.= '"a_number_float": {"<all_channels>": {"<all_locales>": "15.0000"}}, ';
        $expectedValues.= '"a_localized_and_scopable_text_area": {"ecommerce": {"de_DE": "mein text", "fr_FR": "mon text"}}}';

        $this->assertSame(['raw_values' => $expectedValues], $this->getValuesFromDatabase('product_a'));
    }

    public function testDeleteAttributeOnProduct()
    {
        $this->generateToken('mary');
        $product = $this->getProduct('product_a');

        $this->updateProduct($product, [
            'values' => [
                'a_text' => [
                    ['data' => null, 'locale' => null, 'scope' => null]
                ]
            ]
        ]);
        $this->get('pim_catalog.saver.product')->save($product);

        $expectedValues = '{"sku": {"<all_channels>": {"<all_locales>": "product_a"}}, ';
        $expectedValues.= '"a_text": {"<all_channels>": {"<all_locales>": null}}, ';
        $expectedValues.= '"a_number_float": {"<all_channels>": {"<all_locales>": "15.0000"}}, ';
        $expectedValues.= '"a_localized_and_scopable_text_area": {"ecommerce": {"de_DE": "mein text", "fr_FR": "mon text"}}}';

        $this->assertSame(['raw_values' => $expectedValues], $this->getValuesFromDatabase('product_a'));
    }

    public function testAddNewLocalizableAttributeOnProduct()
    {
        $this->generateToken('mary');
        $product = $this->getProduct('product_a');

        $this->updateProduct($product, [
            'values' => [
                'a_localized_and_scopable_text_area' => [
                    ['data' => 'my text', 'locale' => 'en_US', 'scope' => 'ecommerce']
                ]
            ]
        ]);
        $this->get('pim_catalog.saver.product')->save($product);

        $expectedValues = '{"sku": {"<all_channels>": {"<all_locales>": "product_a"}}, ';
        $expectedValues.= '"a_text": {"<all_channels>": {"<all_locales>": "text"}}, ';
        $expectedValues.= '"a_number_float": {"<all_channels>": {"<all_locales>": "15.0000"}}, ';
        $expectedValues.= '"a_localized_and_scopable_text_area": {"ecommerce": {"de_DE": "mein text", "en_US": "my text", "fr_FR": "mon text"}}}';

        $this->assertSame(['raw_values' => $expectedValues], $this->getValuesFromDatabase('product_a'));
    }

    public function testCreateAProductWithAttributeGroupNotFound()
    {
        $this->expectException(UnknownPropertyException::class);
        $this->expectExceptionMessage('Property "not_found" does not exist.');

        $this->generateToken('mary');
        $this->createProduct('product', ['values' => ['not_found' => [['data' => ['optionB'], 'locale' => null, 'scope' => null]]]]);
    }

    public function testCreateAProductWithAttributeGroupNotViewable()
    {
        $this->expectException(UnknownPropertyException::class);
        $this->expectExceptionMessage('Property "a_multi_select" does not exist.');

        $this->generateToken('mary');
        $this->createProduct('product', ['values' => ['a_multi_select' => [['data' => ['optionB'], 'locale' => null, 'scope' => null]]]]);
    }

    public function testCreateAProductWithAttributeGroupOnlyViewable()
    {
        $this->expectException(ResourceAccessDeniedException::class);
        $this->expectExceptionMessage('Attribute "a_number_float" belongs to the attribute group "attributeGroupB" on which you only have view permission.');

        $this->generateToken('mary');
        $this->createProduct('product', ['values' => ['a_number_float' => [['data' => 12.05, 'locale' => null, 'scope' => null]]]]);
    }

    public function testCreateAProductWithAttributeGroupEditable()
    {
        $this->generateToken('mary');
        $product = $this->createProduct('product', ['values' => ['a_text' => [['data' => 'The text', 'locale' => null, 'scope' => null]]]]);

        $this->assertSame($product->getValue('a_text')->getData(), 'The text');
    }

    public function testUpdateAProductWithAttributeGroupNotViewable()
    {
        $this->expectException(UnknownPropertyException::class);
        $this->expectExceptionMessage('Property "a_multi_select" does not exist.');

        $product = $this->saveProduct('product', ['values' => ['a_multi_select' => [['data' => ['optionB'], 'locale' => null, 'scope' => null]]]]);
        $this->generateToken('mary');

        $this->updateProduct($product, [
            'values' => [
                'a_multi_select' => [['data' => ['optionB'], 'locale' => null, 'scope' => null]]
            ]
        ]);
    }

    public function testUpdateAProductWithAttributeGroupOnlyViewableWithChange()
    {
        $this->expectException(ResourceAccessDeniedException::class);
        $this->expectExceptionMessage('Attribute "a_number_float" belongs to the attribute group "attributeGroupB" on which you only have view permission.');

        $product = $this->saveProduct('product', ['values' => ['a_number_float' => [['data' => 12, 'locale' => null, 'scope' => null]]]]);
        $this->generateToken('mary');

        $this->updateProduct($product, [
            'values' => [
                'a_number_float' => [['data' => 14, 'locale' => null, 'scope' => null]]
            ]
        ]);
    }

    public function testUpdateAProductWithAttributeGroupOnlyViewableWithoutChange()
    {
        $product = $this->saveProduct('product', ['values' => ['a_number_float' => [['data' => 12, 'locale' => null, 'scope' => null]]]]);
        $this->generateToken('mary');

        $this->updateProduct($product, [
            'values' => [
                'a_number_float' => [['data' => 12, 'locale' => null, 'scope' => null]]
            ]
        ]);

        $this->assertSame($product->getValue('a_number_float')->getData(), 12);
    }

    public function testUpdateAProductWithAttributeGroupEditable()
    {
        $product = $this->saveProduct('product', ['values' => ['a_text' => [['data' => 'The text', 'locale' => null, 'scope' => null]]]]);
        $this->generateToken('mary');
        $this->updateProduct($product, [
            'values' => [
                'a_text' => [['data' => 'The text bis', 'locale' => null, 'scope' => null]]
            ]
        ]);

        $this->assertSame($product->getValue('a_text')->getData(), 'The text bis');
    }

    public function testCreateAProductWithLocaleNotFound()
    {
        $this->expectException(UnknownPropertyException::class);
        $this->expectExceptionMessage('Attribute "a_localized_and_scopable_text_area" expects an existing and activated locale, "not_found" given.');

        $this->generateToken('mary');
        $this->createProduct('product', ['values' => ['a_localized_and_scopable_text_area' => [['data' => 'text', 'locale' => 'not_found', 'scope' => 'ecommerce']]]]);
    }

    public function testCreateAProductWithLocaleNotViewable()
    {
        $this->expectException(UnknownPropertyException::class);
        $this->expectExceptionMessage('Attribute "a_localized_and_scopable_text_area" expects an existing and activated locale, "de_DE" given');

        $this->generateToken('mary');
        $this->createProduct('product', ['values' => ['a_localized_and_scopable_text_area' => [['data' => 'text', 'locale' => 'de_DE', 'scope' => 'ecommerce']]]]);
    }

    public function testCreateAProductWithLocaleOnlyViewable()
    {
        $this->expectException(ResourceAccessDeniedException::class);
        $this->expectExceptionMessage('You only have a view permission on the locale "fr_FR"');

        $this->generateToken('mary');
        $this->createProduct('product', ['values' => ['a_localized_and_scopable_text_area' => [['data' => 'text', 'locale' => 'fr_FR', 'scope' => 'ecommerce']]]]);
    }

    public function testCreateAProductWithLocaleEditable()
    {
        $this->generateToken('mary');
        $product = $this->createProduct('product', ['values' => ['a_localized_and_scopable_text_area' => [['data' => 'text', 'locale' => 'en_US', 'scope' => 'ecommerce']]]]);

        $this->assertSame($product->getValue('a_localized_and_scopable_text_area', 'en_US', 'ecommerce')->getData(), 'text');
    }

    public function testUpdateAProductWithLocaleNotViewable()
    {
        $this->expectException(UnknownPropertyException::class);
        $this->expectExceptionMessage('Attribute "a_localized_and_scopable_text_area" expects an existing and activated locale, "de_DE" given');

        $product = $this->saveProduct('product', ['values' => ['a_localized_and_scopable_text_area' => [['data' => 'text', 'locale' => 'de_DE', 'scope' => 'ecommerce']]]]);
        $this->generateToken('mary');

        $this->updateProduct($product, ['values' => ['a_localized_and_scopable_text_area' => [['data' => 'text', 'locale' => 'de_DE', 'scope' => 'ecommerce']]]]);
    }

    public function testUpdateAProductWithLocaleOnlyViewableWithChange()
    {
        $this->expectException(ResourceAccessDeniedException::class);
        $this->expectExceptionMessage('You only have a view permission on the locale "fr_FR"');

        $product = $this->saveProduct('product', ['values' => ['a_localized_and_scopable_text_area' => [['data' => 'text', 'locale' => 'fr_FR', 'scope' => 'ecommerce']]]]);
        $this->generateToken('mary');

        $this->updateProduct($product, ['values' => ['a_localized_and_scopable_text_area' => [['data' => 'text FR', 'locale' => 'fr_FR', 'scope' => 'ecommerce']]]]);
    }

    public function testUpdateAProductWithLocaleOnlyViewableWithoutChange()
    {
        $product = $this->saveProduct('product', ['values' => ['a_localized_and_scopable_text_area' => [['data' => 'text', 'locale' => 'fr_FR', 'scope' => 'ecommerce']]]]);
        $this->generateToken('mary');

        $this->updateProduct($product, ['values' => ['a_localized_and_scopable_text_area' => [['data' => 'text', 'locale' => 'fr_FR', 'scope' => 'ecommerce']]]]);
        $this->assertSame($product->getValue('a_localized_and_scopable_text_area', 'fr_FR', 'ecommerce')->getData(), 'text');
    }

    public function testUpdateAProductWithLocaleEditable()
    {
        $product = $this->saveProduct('product', ['values' => ['a_localized_and_scopable_text_area' => [['data' => 'text', 'locale' => 'en_US', 'scope' => 'ecommerce']]]]);
        $this->generateToken('mary');

        $this->updateProduct($product, ['values' => ['a_localized_and_scopable_text_area' => [['data' => 'text EN', 'locale' => 'en_US', 'scope' => 'ecommerce']]]]);
        $this->assertSame($product->getValue('a_localized_and_scopable_text_area', 'en_US', 'ecommerce')->getData(), 'text EN');
    }
}
