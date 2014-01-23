<?php

namespace Pim\Bundle\ImportExportBundle\Tests\Unit\Normalizer;

use Pim\Bundle\ImportExportBundle\Normalizer\FlatAssociationTypeNormalizer;
use Pim\Bundle\ImportExportBundle\Normalizer\FlatTranslationNormalizer;
use Pim\Bundle\CatalogBundle\Entity\AssociationType;

/**
 * Association normalizer test
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FlatAssociationTypeNormalizerTest extends AssociationTypeNormalizerTest
{
    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->normalizer = new FlatAssociationTypeNormalizer(new FlatTranslationNormalizer());
        $this->format     = 'csv';
    }

    /**
     * {@inheritdoc}
     */
    public static function getSupportNormalizationData()
    {
        return [
            ['Pim\Bundle\CatalogBundle\Entity\AssociationType', 'csv', true],
            ['Pim\Bundle\CatalogBundle\Entity\AssociationType', 'xml', false],
            ['Pim\Bundle\CatalogBundle\Entity\AssociationType', 'json', false],
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
                    'code'  => 'mycode',
                    'label-en_US' => 'My label',
                    'label-fr_FR' => 'Mon étiquette'
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
