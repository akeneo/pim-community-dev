<?php

namespace Pim\Component\Catalog\tests\integration\Normalizer\Standard;

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
            'code' => 'variant_clothing_color_and_size',
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
                    'attributes' => ['name', 'image_1', 'variation_image', 'composition', 'color'],
                ],
                [
                    'level' => 2,
                    'axes' => ['size'],
                    'attributes' => ['sku', 'weight', 'size', 'EAN'],
                ],
            ],
        ];

        $repository = $this->get('pim_catalog.repository.family_variant');
        $serializer = $this->get('pim_serializer');

        $result = $serializer->normalize(
            $repository->findOneByIdentifier('variant_clothing_color_and_size'),
            'standard'
        );

        $this->assertSame($expected, $result);
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration(): Configuration
    {
        return new Configuration([Configuration::getFunctionalCatalog('catalog_modeling')]);
    }
}
