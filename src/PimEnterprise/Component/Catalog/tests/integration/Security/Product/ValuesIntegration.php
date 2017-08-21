<?php

namespace PimEnterprise\Component\Catalog\tests\integration\Security\Product;

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
 */
class ValuesIntegration extends AbstractSecurityTestCase
{
    protected function setUp()
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
                    'fr_FR' => 'mon text',
                    'de_DE' => 'mein text',
                ]
            ],
            'a_date' => [
                '<all_channels>' => [
                    '<all_locales>' => '2017-03-03T00:00:00+01:00'
                ]
            ]
        ]);
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

        $this->assertSame($product->getRawValues(), [
            'sku' => [
                '<all_channels>' => [
                    '<all_locales>' => 'product_a'
                ]
            ],
            'a_number_float' => [
                '<all_channels>' => [
                    '<all_locales>' => '15.0000'
                ]
            ],
            'a_localized_and_scopable_text_area' => [
                'ecommerce' => [
                    'fr_FR' => 'mon text',
                    'de_DE' => 'mein text',
                ]
            ],
            'a_text' => [
                '<all_channels>' => [
                    '<all_locales>' => null
                ]
            ]
        ]);
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
                    'fr_FR' => 'mon text',
                    'de_DE' => 'mein text',
                    'en_US' => 'my text',
                ]
            ]
        ]);
    }

    /**
     * @expectedException \Akeneo\Component\StorageUtils\Exception\UnknownPropertyException
     * @expectedExceptionMessage Property "not_found" does not exist.
     */
    public function testCreateAProductWithAttributeGroupNotFound()
    {
        $this->generateToken('mary');
        $this->createProduct('product', ['values' => ['not_found' => [['data' => ['optionB'], 'locale' => null, 'scope' => null]]]]);
    }

    /**
     * @expectedException \Akeneo\Component\StorageUtils\Exception\UnknownPropertyException
     * @expectedExceptionMessage Property "a_multi_select" does not exist.
     */
    public function testCreateAProductWithAttributeGroupNotViewable()
    {
        $this->generateToken('mary');
        $this->createProduct('product', ['values' => ['a_multi_select' => [['data' => ['optionB'], 'locale' => null, 'scope' => null]]]]);
    }

    /**
     * @expectedException \PimEnterprise\Component\Security\Exception\ResourceAccessDeniedException
     * @expectedExceptionMessage Attribute "a_number_float" belongs to the attribute group "attributeGroupB" on which you only have view permission.
     */
    public function testCreateAProductWithAttributeGroupOnlyViewable()
    {
        $this->generateToken('mary');
        $this->createProduct('product', ['values' => ['a_number_float' => [['data' => 12.05, 'locale' => null, 'scope' => null]]]]);
    }

    public function testCreateAProductWithAttributeGroupEditable()
    {
        $this->generateToken('mary');
        $product = $this->createProduct('product', ['values' => ['a_text' => [['data' => 'The text', 'locale' => null, 'scope' => null]]]]);

        $this->assertSame($product->getValue('a_text')->getData(), 'The text');
    }

    /**
     * @expectedException \Akeneo\Component\StorageUtils\Exception\UnknownPropertyException
     * @expectedExceptionMessage Property "a_multi_select" does not exist.
     */
    public function testUpdateAProductWithAttributeGroupNotViewable()
    {
        $product = $this->saveProduct('product', ['values' => ['a_multi_select' => [['data' => ['optionB'], 'locale' => null, 'scope' => null]]]]);
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
    public function testUpdateAProductWithAttributeGroupOnlyViewableWithChange()
    {
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

    /**
     * @expectedException \Akeneo\Component\StorageUtils\Exception\UnknownPropertyException
     * @expectedExceptionMessage Attribute "a_localized_and_scopable_text_area" expects an existing and activated locale, "not_found" given.
     */
    public function testCreateAProductWithLocaleNotFound()
    {
        $this->generateToken('mary');
        $this->createProduct('product', ['values' => ['a_localized_and_scopable_text_area' => [['data' => 'text', 'locale' => 'not_found', 'scope' => 'ecommerce']]]]);
    }

    /**
     * @expectedException \Akeneo\Component\StorageUtils\Exception\UnknownPropertyException
     * @expectedExceptionMessage Attribute "a_localized_and_scopable_text_area" expects an existing and activated locale, "de_DE" given
     */
    public function testCreateAProductWithLocaleNotViewable()
    {
        $this->generateToken('mary');
        $this->createProduct('product', ['values' => ['a_localized_and_scopable_text_area' => [['data' => 'text', 'locale' => 'de_DE', 'scope' => 'ecommerce']]]]);
    }

    /**
     * @expectedException \PimEnterprise\Component\Security\Exception\ResourceAccessDeniedException
     * @expectedExceptionMessage You only have a view permission on the locale "fr_FR"
     */
    public function testCreateAProductWithLocaleOnlyViewable()
    {
        $this->generateToken('mary');
        $this->createProduct('product', ['values' => ['a_localized_and_scopable_text_area' => [['data' => 'text', 'locale' => 'fr_FR', 'scope' => 'ecommerce']]]]);
    }

    public function testCreateAProductWithLocaleEditable()
    {
        $this->generateToken('mary');
        $product = $this->createProduct('product', ['values' => ['a_localized_and_scopable_text_area' => [['data' => 'text', 'locale' => 'en_US', 'scope' => 'ecommerce']]]]);

        $this->assertSame($product->getValue('a_localized_and_scopable_text_area', 'en_US', 'ecommerce')->getData(), 'text');
    }

    /**
     * @expectedException \Akeneo\Component\StorageUtils\Exception\UnknownPropertyException
     * @expectedExceptionMessage Attribute "a_localized_and_scopable_text_area" expects an existing and activated locale, "de_DE" given
     */
    public function testUpdateAProductWithLocaleNotViewable()
    {
        $product = $this->saveProduct('product', ['values' => ['a_localized_and_scopable_text_area' => [['data' => 'text', 'locale' => 'de_DE', 'scope' => 'ecommerce']]]]);
        $this->generateToken('mary');

        $this->updateProduct($product, ['values' => ['a_localized_and_scopable_text_area' => [['data' => 'text', 'locale' => 'de_DE', 'scope' => 'ecommerce']]]]);
    }

    /**
     * @expectedException \PimEnterprise\Component\Security\Exception\ResourceAccessDeniedException
     * @expectedExceptionMessage You only have a view permission on the locale "fr_FR"
     */
    public function testUpdateAProductWithLocaleOnlyViewableWithChange()
    {
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
