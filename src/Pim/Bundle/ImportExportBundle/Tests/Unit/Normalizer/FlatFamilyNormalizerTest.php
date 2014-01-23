<?php

namespace Pim\Bundle\ImportExportBundle\Tests\Unit\Normalizer;

use Pim\Bundle\ImportExportBundle\Normalizer\FlatFamilyNormalizer;
use Pim\Bundle\ImportExportBundle\Normalizer\FlatTranslationNormalizer;
use Pim\Bundle\CatalogBundle\Entity\Family;

/**
 * Family normalizer test
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FlatFamilyNormalizerTest extends FamilyNormalizerTest
{
    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->normalizer = new FlatFamilyNormalizer(new FlatTranslationNormalizer());
        $this->format     = 'csv';
    }

    /**
     * {@inheritdoc}
     */
    public static function getSupportNormalizationData()
    {
        return [
            ['Pim\Bundle\CatalogBundle\Entity\Family', 'csv', true],
            ['Pim\Bundle\CatalogBundle\Entity\Family', 'xml', false],
            ['Pim\Bundle\CatalogBundle\Entity\Family', 'json', false],
            ['stdClass', 'csv', false],
            ['stdClass', 'xml', false],
            ['stdClass', 'json', false],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public static function getNormalizeData()
    {
        return [
            [
                [
                    'code'             => 'mycode',
                    'label-en_US'      => 'My label',
                    'label-fr_FR'      => 'Mon étiquette',
                    'attributes'       => 'attribute1,attribute2,attribute3',
                    'attributeAsLabel' => 'attribute1',
                    'requirements'     => 'channel1:attribute1,attribute2|channel2:attribute1,attribute3',
                ]
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function getLabels($data)
    {
        return [
            'en_US' => $data['label-en_US'],
            'fr_FR' => $data['label-fr_FR']
        ];
    }
}
