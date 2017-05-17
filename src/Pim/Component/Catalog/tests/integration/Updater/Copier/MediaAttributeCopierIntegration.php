<?php

namespace tests\integration\Pim\Component\Catalog\Updater\Copier;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\MediaSanitizer;
use Akeneo\Test\Integration\TestCase;
use Pim\Component\Catalog\Model\ProductInterface;

/**
 * @author    Alexandre Hocquard <alexandre.hocquard@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MediaAttributeCopierIntegration extends TestCase
{
    /**
     * {@inheritdoc}
     */
    protected function getConfiguration()
    {
        return new Configuration([Configuration::getTechnicalCatalogPath()]);
    }

    public function testCopyToMediaWithLocale()
    {
        $sku = 'test_localizable_media';
        $parameters = [
            'values' => [
                'a_scopable_image' => [
                    [
                        'data'   => $this->getParameter('kernel.root_dir').'/../features/Context/fixtures/SNKRS-1R.png',
                        'locale' => null,
                        'scope'  => 'tablet',
                    ],
                ],
            ],
        ];

        $product = $this->createProduct($sku, $parameters);

        $fields  = [
            'from' => 'a_scopable_image',
            'to'   => 'a_localizable_image',
        ];

        $options = [
            'from_locale' => null,
            'from_scope'  => 'tablet',
            'to_locale'   => 'fr_FR',
            'to_scope'    => null,
        ];

        $result = [
            [
                'locale' => 'fr_FR',
                'scope'  => null,
                'data'   => 'd/5/e/1/d5e1aeb5149a8a721e567952c895d20ffef8c6d9_SNKRS_1R.png',
            ],
        ];

        $this->assertCopyMedia($product, $fields, $options, $result);
    }

    public function testCopyToMediaWithChannel()
    {
        $sku = 'test_scopable_media';
        $parameters = [
            'values' => [
                'a_localizable_image' => [
                    [
                        'data'   => $this->getParameter('kernel.root_dir').'/../features/Context/fixtures/SNKRS-1R.png',
                        'locale' => 'fr_FR',
                        'scope'  => null,
                    ],
                ],
            ],
        ];

        $product = $this->createProduct($sku, $parameters);

        $fields  = [
            'from' => 'a_localizable_image',
            'to'   => 'a_scopable_image',
        ];

        $options = [
            'from_locale' => 'fr_FR',
            'from_scope'  => null,
            'to_locale'   => null,
            'to_scope'    => 'ecommerce',
        ];

        $result = [
            [
                'locale' => null,
                'scope'  => 'ecommerce',
                'data'   => 'd/5/e/1/d5e1aeb5149a8a721e567952c895d20ffef8c6d9_SNKRS_1R.png',
            ],
        ];

        $this->assertCopyMedia($product, $fields, $options, $result);
    }

    public function testCopyToMediaWithChannelAndLocale()
    {
        $sku = 'test_scopable_localizable_media';
        $parameters = [
            'values' => [
                'an_image' => [
                    [
                        'data'   => $this->getParameter('kernel.root_dir').'/../features/Context/fixtures/SNKRS-1R.png',
                        'locale' => null,
                        'scope'  => null,
                    ],
                ],
            ],
        ];

        $product = $this->createProduct($sku, $parameters);

        $fields  = [
            'from' => 'an_image',
            'to'   => 'a_localizable_scopable_image',
        ];

        $options = [
            'from_locale' => null,
            'from_scope'  => null,
            'to_locale'   => 'fr_FR',
            'to_scope'    => 'tablet',
        ];

        $result = [
            [
                'locale' => 'fr_FR',
                'scope'  => 'tablet',
                'data'   => 'd/5/e/1/d5e1aeb5149a8a721e567952c895d20ffef8c6d9_SNKRS_1R.png',
            ],
        ];

        $this->assertCopyMedia($product, $fields, $options, $result);
    }

    /**
     * Copy a media attribute in another one and assert it is well copied.
     *
     * @param ProductInterface $product
     * @param array            $fields
     * @param array            $options
     * @param array            $result
     */
    protected function assertCopyMedia(ProductInterface $product, array $fields, array $options, array $result)
    {
        $defaultOptions = [
            'from_locale' => null,
            'to_locale'   => null,
            'from_scope'  => null,
            'to_scope'    => null,
        ];

        $options = array_merge($defaultOptions, $options);

        $productCopier = $this->get('pim_catalog.updater.product_property_copier');

        $productCopier->copyData(
            $product,
            $product,
            $fields['from'],
            $fields['to'],
            $options
        );

        $this->get('pim_catalog.saver.product')->save($product);

        $standardProduct = $this->get('pim_serializer')->normalize($product, 'standard');


        $result = $this->sanitizeMediaAttributeData($result);
        $standardValues = $this->sanitizeMediaAttributeData($standardProduct['values'][$fields['to']]);

        $this->assertEquals($result, $standardValues);
    }

    /**
     * Create a product.
     *
     * @param $sku
     * @param $parameters
     * @return ProductInterface
     */
    protected function createProduct($sku, $parameters)
    {
        $productUpdater = $this->get('pim_catalog.updater.product');

        $product = $this->get('pim_catalog.builder.product')->createProduct($sku);
        $productUpdater->update($product, $parameters);

        $this->get('pim_catalog.saver.product')->save($product);

        return $product;
    }

    /**
     * Replaces media attributes data in the $data array by self::MEDIA_ATTRIBUTE_DATA_COMPARISON.
     *
     * @param array $data
     *
     * @return array
     */
    protected function sanitizeMediaAttributeData(array $data)
    {
        foreach ($data as $index => $value) {
            $data[$index]['data'] = MediaSanitizer::sanitize($value['data']);
        }

        return $data;
    }
}
