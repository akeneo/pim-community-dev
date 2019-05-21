<?php

namespace AkeneoTest\Pim\Enrichment\Integration\Updater\Copier;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Test\IntegrationTestsBundle\Sanitizer\MediaSanitizer;

/**
 * @author    Alexandre Hocquard <alexandre.hocquard@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MediaAttributeCopierIntegration extends AbstractCopierTestCase
{
    public function testCopyToMediaWithLocale()
    {
        $sku = 'test_localizable_media';
        $parameters = [
            'values' => [
                'a_scopable_image' => [
                    [
                        'data'   => $this->getFileInfoKey($this->getParameter('kernel.root_dir').'/../tests/legacy/features/Context/fixtures/SNKRS-1R.png'),
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
                        'data'   => $this->getFileInfoKey($this->getParameter('kernel.root_dir').'/../tests/legacy/features/Context/fixtures/SNKRS-1R.png'),
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
                        'data'   => $this->getFileInfoKey($this->getParameter('kernel.root_dir').'/../tests/legacy/features/Context/fixtures/SNKRS-1R.png'),
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

        $productCopier = $this->get('pim_catalog.updater.property_copier');

        $productCopier->copyData(
            $product,
            $product,
            $fields['from'],
            $fields['to'],
            $options
        );

        $this->get('pim_catalog.saver.product')->save($product);

        $standardProduct = $this->get('pim_standard_format_serializer')->normalize($product, 'standard');


        $result = $this->sanitizeMediaAttributeData($result);
        $standardValues = $this->sanitizeMediaAttributeData($standardProduct['values'][$fields['to']]);

        $this->assertEquals($result, $standardValues);
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
