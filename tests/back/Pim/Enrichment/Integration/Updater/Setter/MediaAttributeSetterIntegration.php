<?php

namespace AkeneoTest\Pim\Enrichment\Integration\Updater\Setter;

use Akeneo\Test\Integration\TestCase;
use Akeneo\Test\IntegrationTestsBundle\Sanitizer\MediaSanitizer;

/**
 * @author    Alexandre Hocquard <alexandre.hocquard@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MediaAttributeSetterIntegration extends TestCase
{
    /**
     * {@inheritdoc}
     */
    protected function getConfiguration()
    {
        return $this->catalog->useTechnicalCatalog();
    }

    public function testLocalizableMedia()
    {
        $attributeName = 'a_localizable_image';

        $parameters = [
            'values' => [
                $attributeName => [
                    [
                        'data'   => $this->getFileInfoKey($this->getParameter('kernel.root_dir').'/../tests/legacy/features/Context/fixtures/SNKRS-1R.png'),
                        'locale' => 'fr_FR',
                        'scope'  => null,
                    ],
                ],
            ],
        ];

        $result = [
            [
                'locale' => 'fr_FR',
                'scope'  => null,
                'data'   => 'd/5/e/1/d5e1aeb5149a8a721e567952c895d20ffef8c6d9_SNKRS_1R.png',
            ],
        ];

        $this->assertCommandMedia($parameters, $result, $attributeName);
    }

    public function testMediaWithChannel()
    {
        $attributeName = 'a_scopable_image';

        $parameters = [
            'values' => [
                $attributeName => [
                    [
                        'data'   => $this->getFileInfoKey($this->getParameter('kernel.root_dir').'/../tests/legacy/features/Context/fixtures/SNKRS-1C-t.png'),
                        'locale' => null,
                        'scope'  => 'tablet',
                    ],
                ],
            ],
        ];

        $result = [
            [
                'locale' => null,
                'scope'  => 'tablet',
                'data'   => 'd/5/e/1/d5e1aeb5149a8a721e567952c895d20ffef8c6d9_SNKRS_1R.png',
            ],
        ];

        $this->assertCommandMedia($parameters, $result, $attributeName);
    }

    public function testMediaWithLocaleAndChannel()
    {
        $attributeName = 'a_localizable_scopable_image';

        $parameters = [
            'values' => [
                $attributeName => [
                    [
                        'data'   => $this->getFileInfoKey($this->getParameter('kernel.root_dir').'/../tests/legacy/features/Context/fixtures/SNKRS-1R.png'),
                        'locale' => 'fr_FR',
                        'scope'  => 'tablet',
                    ],
                ],
            ],
        ];

        $result = [
            [
                'locale' => 'fr_FR',
                'scope'  => 'tablet',
                'data'   => 'd/5/e/1/d5e1aeb5149a8a721e567952c895d20ffef8c6d9_SNKRS_1R.png',
            ],
        ];

        $this->assertCommandMedia($parameters, $result, $attributeName);
    }

    public function testIsSameMedia()
    {
        $attributeName = 'a_localizable_scopable_image';

        $parameters = [
            'values' => [
                $attributeName => [
                    [
                        'data'   => $this->getFileInfoKey($this->getParameter('kernel.root_dir').'/../tests/legacy/features/Context/fixtures/SNKRS-1R.png'),
                        'locale' => 'fr_FR',
                        'scope'  => 'tablet',
                    ],
                ],
                $attributeName => [
                    [
                        'data'   => $this->getFileInfoKey($this->getParameter('kernel.root_dir').'/../tests/legacy/features/Context/fixtures/SNKRS-1R.png'),
                        'locale' => 'fr_FR',
                        'scope'  => 'tablet',
                    ],
                ],
            ],
        ];

        $result = [
            [
                'locale' => 'fr_FR',
                'scope'  => 'tablet',
                'data'   => 'd/5/e/1/d5e1aeb5149a8a721e567952c895d20ffef8c6d9_SNKRS_1R.png',
            ],
        ];

        $this->assertCommandMedia($parameters, $result, $attributeName);
    }

    protected function assertCommandMedia(array $parameters, array $result, $attributeName)
    {
        $productUpdater = $this->get('pim_catalog.updater.product');

        $product = $this->get('pim_catalog.builder.product')->createProduct('product_media');
        $productUpdater->update($product, $parameters);

        $this->get('pim_catalog.saver.product')->save($product);

        $standardProduct = $this->get('pim_standard_format_serializer')->normalize($product, 'standard');

        $result = $this->sanitizeMediaAttributeData($result);
        $standardValues = $this->sanitizeMediaAttributeData($standardProduct['values'][$attributeName]);

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
