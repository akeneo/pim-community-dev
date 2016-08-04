<?php

namespace Pim\Component\Catalog\Tests\Integration\StandardFormat;

use Pim\Component\Catalog\Model\ProductInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;

class ProductStandardFormatInt extends KernelTestCase
{
    const DATE_FIELD_COMPARISON = 'this is a date formatted to ISO-8601';
    const MEDIA_ATTRIBUTE_DATA_COMPARISON = 'this is a media identifier';

    const DATE_FIELD_PATTERN = '#[0-9]{4}-[0-9]{2}-[0-9]{2}T[0-9]{2}:[0-9]{2}:[0-9]{2}\+[0-9]{2}:[0-9]{2}#';
    const MEDIA_ATTRIBUTE_DATA_PATTERN = '#[0-9a-z]/[0-9a-z]/[0-9a-z]/[0-9a-z]/[0-9a-z]{40}_\w+\.[a-zA-Z]+#';

    /** @var ContainerInterface */
    private $container;

    /** @var bool */
    private static $dbLoaded = false;

    public function setUp()
    {
        self::bootKernel();
        $this->container = self::$kernel->getContainer();

        if (!self::$dbLoaded) {
            $em = $this->container->get('doctrine.orm.entity_manager');
            $em->getConnection()->executeQuery(file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . 'bdd.sql'));
            self::$dbLoaded = true;
        }
    }

    public function testEmptyDisabledProduct()
    {
        /*
        $attribute = new Attribute();
        $attribute->setCode('sku');
        $attribute->setBackendType(AttributeTypes::BACKEND_TYPE_VARCHAR);
        $attribute->setAttributeType(AttributeTypes::IDENTIFIER);

        $product = new Product();
        $product->setEnabled(false);
        $productValue = new ProductValue();
        $productValue->setAttribute($attribute);
        $productValue->setData('bar');
        $product->addValue($productValue);

        // 1. it's very complicated to create a full catalog just from the object
        // 2. we need to really save the object in DB because some of our/Doctrine events (created/updated fields)
        //    are used internally. That means, at the end, we need a DB... Or mock it, which is painful too.
        */

        $repository = $this->container->get('pim_catalog.repository.product');
        $product = $repository->findOneByIdentifier('bar');

        $expected = [
            "family"        => null,
            "groups"        => [],
            "variant_group" => null,
            "categories"    => [],
            "enabled"       => false,
            "values"        => [
                "sku" => [
                    0 => [
                        "locale" => null,
                        "scope"  => null,
                        "data"   => "bar",
                    ],
                ],
            ],
            "created"       => "2016-06-14T13:12:50+02:00",
            "updated"       => "2016-06-14T13:12:50+02:00",
            "associations"  => [],
        ];

        $this->assertStandardFormat($product, $expected);
    }

    public function testEmptyEnabledProduct()
    {
        $repository = $this->container->get('pim_catalog.repository.product');
        $product = $repository->findOneByIdentifier('baz');

        $expected = [
            "family"        => null,
            "groups"        => [],
            "variant_group" => null,
            "categories"    => [],
            "enabled"       => true,
            "values"        => [
                "sku" => [
                    0 => [
                        "locale" => null,
                        "scope"  => null,
                        "data"   => "baz",
                    ],
                ],
            ],
            "created"       => "2016-06-14T13:12:50+02:00",
            "updated"       => "2016-06-14T13:12:50+02:00",
            "associations"  => [],
        ];

        $this->assertStandardFormat($product, $expected);
    }

    public function testProductWithAllAttributes()
    {
        $repository = $this->container->get('pim_catalog.repository.product');
        $product = $repository->findOneByIdentifier('foo');

        $expected =
            [
                "family"        => "familyA",
                "groups"        => ["groupA", "groupB",],
                "variant_group" => "variantA",
                "categories"    => ["categoryA1", "categoryB",],
                "enabled"       => true,
                "values"        => [
                    "sku"                                => [
                        ["locale" => null, "scope" => null, "data" => "foo",],
                    ],
                    "a_file"                             => [
                        [
                            "locale" => null,
                            "scope"  => null,
                            "data"   => "4/d/e/b/4deb535f0979dea59cf34661e22336459a56bed3_fileA.txt",
                        ],
                    ],
                    "an_image"                           => [
                        [
                            "locale" => null,
                            "scope"  => null,
                            "data"   => "1/5/7/5/15757827125efa686c1c0f1e7930ca0c528f1c2c_imageA.jpg",
                        ],
                    ],
                    "a_date"                             => [
                        ["locale" => null, "scope" => null, "data" => "2016-06-13T00:00:00+02:00",],
                    ],
                    "a_metric"                           => [
                        [
                            "locale" => null,
                            "scope"  => null,
                            "data"   => ["data" => "987654321987.123456789123", "unit" => "KILOWATT",],
                        ],
                    ],
                    "a_multi_select"                     => [
                        ["locale" => null, "scope" => null, "data" => ["optionA", "optionB",],],
                    ],
                    "a_number_float"                     => [
                        ["locale" => null, "scope" => null, "data" => "12.5678",],
                    ],
                    "a_number_float_negative"            => [
                        ["locale" => null, "scope" => null, "data" => "-99.8732",],
                    ],
                    "a_number_integer"                   => [["locale" => null, "scope" => null, "data" => "42",],],
                    "a_price"                            => [
                        [
                            "locale" => null,
                            "scope"  => null,
                            "data"   => [
                                ["data" => "45", "currency" => "USD",],
                                ["data" => "56.53", "currency" => "EUR",],
                            ],
                        ],
                    ],
                    "a_ref_data_multi_select"            => [
                        ["locale" => null, "scope" => null, "data" => ["fabricA", "fabricB",],],
                    ],
                    "a_ref_data_simple_select"           => [
                        ["locale" => null, "scope" => null, "data" => "colorB",],
                    ],
                    "a_simple_select"                    => [
                        ["locale" => null, "scope" => null, "data" => "optionB",],
                    ],
                    "a_text"                             => [
                        [
                            "locale" => null,
                            "scope"  => null,
                            "data"   => "this is a text",
                        ],
                    ],
                    "a_text_area"                        => [
                        [
                            "locale" => null,
                            "scope"  => null,
                            "data"   => 'this is a very very very very very long  text',
                        ],
                    ],
                    "a_yes_no"                           => [
                        ["locale" => null, "scope" => null, "data" => true,],
                    ],
                    "a_localizable_image"                => [
                        [
                            "locale" => "en_US",
                            "scope"  => null,
                            "data"   => "6/2/e/3/62e376e75300d27bfec78878db4d30ff1490bc53_imageB_en_US.jpg",
                        ],
                        [
                            "locale" => "fr_FR",
                            "scope"  => null,
                            "data"   => "0/f/5/0/0f5058de76f68446bb6b2371f19cd2234b245c00_imageB_fr_FR.jpg",
                        ],
                    ],
                    "a_scopable_price"                   => [
                        [
                            "locale" => null,
                            "scope"  => "ecommerce",
                            "data"   => [
                                ["data" => "15", "currency" => "EUR",],
                                ["data" => "20", "currency" => "USD",],
                            ],
                        ],
                        [
                            "locale" => null,
                            "scope"  => "tablet",
                            "data"   => [
                                ["data" => "17", "currency" => "EUR",],
                                ["data" => "24", "currency" => "USD",],
                            ],
                        ],
                    ],
                    "a_localized_and_scopable_text_area" => [
                        [
                            "locale" => "en_US",
                            "scope"  => "ecommerce",
                            "data"   => "a text area for eccommerce in English",
                        ],
                        ["locale" => "en_US", "scope" => "tablet", "data" => "a text area for tablets in English",],
                        [
                            "locale" => "fr_FR",
                            "scope"  => "tablet",
                            "data"   => "une zone de texte pour les tablettes en français",
                        ],
                    ],
                ],
                "created"       => "2016-06-14T13:12:50+02:00",
                "updated"       => "2016-06-14T13:12:50+02:00",
                "associations"  => [
                    "PACK"   => ["products" => ["bar", "baz",],],
                    "UPSELL" => ["groups" => ["groupA",],],
                    "X_SELL" => ["groups" => ["groupB",], "products" => ["bar",],],
                ],
            ];

        $this->assertStandardFormat($product, $expected);
    }

    /**
     * @param ProductInterface $product
     * @param array            $expected
     */
    private function assertStandardFormat(ProductInterface $product, array $expected)
    {
        $result = $this->normalizeProductToStandardFormat($product);
        $result = $this->sanitizeDateFields($result);
        $result = $this->sanitizeMediaAttributeData($result);

        $expected = $this->sanitizeDateFields($expected);
        $expected = $this->sanitizeMediaAttributeData($expected);

        $this->assertEquals($expected, $result);
    }

    /**
     * @param ProductInterface $product
     *
     * @return array
     */
    private function normalizeProductToStandardFormat(ProductInterface $product)
    {
        $serializer = $this->container->get('pim_serializer');

        return $serializer->normalize($product, 'standard');
    }

    /**
     * Replaces dates fields (created/updated) in the $data array by self::DATE_FIELD_COMPARISON.
     *
     * @param array $data
     *
     * @return array
     */
    private function sanitizeDateFields(array $data)
    {
        if ($this->assertDateFieldPattern($data['created']) &&
            $this->assertDateFieldPattern($data['updated'])
        ) {
            $data['created'] = self::DATE_FIELD_COMPARISON;
            $data['updated'] = self::DATE_FIELD_COMPARISON;
        }

        return $data;
    }

    /**
     * Replaces media attributes data in the $data array by self::MEDIA_ATTRIBUTE_DATA_COMPARISON.
     *
     * @param array $data
     *
     * @return array
     */
    private function sanitizeMediaAttributeData(array $data)
    {
        foreach ($data['values'] as $attributeCode => $values) {
            if (1 === preg_match('/.*(file|image).*/', $attributeCode)) {
                foreach ($values as $index => $value) {
                    if ($this->assertMediaAttributeDataPattern($value['data'])) {
                        $data['values'][$attributeCode][$index]['data'] = self::MEDIA_ATTRIBUTE_DATA_COMPARISON;
                    }
                }
            }
        }

        return $data;
    }

    /**
     * @param string $field
     *
     * @return bool
     */
    private function assertDateFieldPattern($field)
    {
        return 1 === preg_match(self::DATE_FIELD_PATTERN, $field);
    }

    /**
     * @param string $data
     *
     * @return bool
     */
    private function assertMediaAttributeDataPattern($data)
    {
        return 1 === preg_match(self::MEDIA_ATTRIBUTE_DATA_PATTERN, $data);
    }
}
