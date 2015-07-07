<?php

namespace Pim\Bundle\TransformBundle\Tests\Unit\Normalizer\Flat;

use Pim\Bundle\TransformBundle\Normalizer\Flat\JobInstanceNormalizer;
use Pim\Bundle\TransformBundle\Tests\Unit\Normalizer\Structured;

/**
 * Test related class
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class JobInstanceNormalizerTest extends Structured\JobInstanceNormalizerTest
{
    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->normalizer = new JobInstanceNormalizer();
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
