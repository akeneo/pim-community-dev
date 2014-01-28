<?php

namespace Pim\Bundle\TransformBundle\Tests\Unit\Normalizer;

use Pim\Bundle\TransformBundle\Normalizer\FlatAssociationTypeNormalizer;
use Pim\Bundle\TransformBundle\Normalizer\FlatTranslationNormalizer;
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
        return array(
            array('Pim\Bundle\CatalogBundle\Entity\AssociationType', 'csv', true),
            array('Pim\Bundle\CatalogBundle\Entity\AssociationType', 'xml', false),
            array('Pim\Bundle\CatalogBundle\Entity\AssociationType', 'json', false),
            array('stdClass', 'csv', false),
            array('stdClass', 'xml', false),
            array('stdClass', 'json', false),
        );
    }

    /**
     * {@inheritdoc}
     */
    public static function getNormalizeData()
    {
        return array(
            array(
                array(
                    'code'  => 'mycode',
                    'label-en_US' => 'My label',
                    'label-fr_FR' => 'Mon étiquette'
                )
            ),
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function getLabels($data)
    {
        return array(
            'en_US' => $data['label-en_US'],
            'fr_FR' => $data['label-fr_FR']
        );
    }
}
