<?php

namespace Pim\Bundle\TransformBundle\Tests\Unit\Normalizer;

use Akeneo\Bundle\BatchBundle\Entity\JobInstance;
use Pim\Bundle\TransformBundle\Normalizer\FlatJobInstanceNormalizer;

/**
 * Test related class
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FlatJobInstanceNormalizerTest extends JobInstanceNormalizerTest
{
    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->normalizer = new FlatJobInstanceNormalizer();
        $this->format     = 'csv';
    }

    /**
     * {@inheritdoc}
     */
    public static function getSupportNormalizationData()
    {
        return array(
            array('Akeneo\Bundle\BatchBundle\Entity\JobInstance', 'json', false),
            array('Akeneo\Bundle\BatchBundle\Entity\JobInstance', 'xml', false),
            array('Akeneo\Bundle\BatchBundle\Entity\JobInstance', 'csv', true),
            array('stdClass', 'json', false),
            array('stdClass', 'xml', false),
            array('stdClass', 'csv', false),
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
                    'code'           => 'my_import',
                    'type'           => 'IMPORT',
                    'label'          => 'My import',
                    'connector'      => 'Akeneo',
                    'configuration'  => '[]'
                ),
            ),
        );
    }
}
