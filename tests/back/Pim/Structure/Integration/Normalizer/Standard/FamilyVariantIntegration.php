<?php

namespace AkeneoTest\Pim\Structure\Integration\Normalizer\Standard;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;

/**
 * @author    Damien Carcel (damien.carcel@akeneo.com)
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class FamilyVariantIntegration extends TestCase
{
    public function testFamilyVariant()
    {
        $expected = [
            'code' => 'clothing_color_size',
            'labels' => [
                'de_DE' => 'Kleidung nach Farbe und Größe',
                'en_US' => 'Clothing by color and size',
                'fr_FR' => 'Vêtements par couleur et taille',
            ],
            'family' => 'clothing',
            'variant_attribute_sets' => [
                [
                    'level' => 1,
                    'axes' => ['color'],
                    'attributes' => ['variation_name', 'variation_image', 'composition', 'color', 'material'],
                ],
                [
                    'level' => 2,
                    'axes' => ['size'],
                    'attributes' => ['sku', 'weight', 'size', 'ean'],
                ],
            ],
        ];

        $repository = $this->get('pim_catalog.repository.family_variant');
        $serializer = $this->get('pim_standard_format_serializer');

        $result = $serializer->normalize(
            $repository->findOneByIdentifier('clothing_color_size'),
            'standard'
        );

        $this->assertSame($expected, $result);
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useFunctionalCatalog('catalog_modeling');
    }
}
