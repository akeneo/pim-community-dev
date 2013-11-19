<?php

namespace Pim\Bundle\ImportExportBundle\Tests\Unit\Normalizer;

use Oro\Bundle\BatchBundle\Entity\JobInstance;
use Pim\Bundle\ImportExportBundle\Normalizer\FlatJobInstanceNormalizer;

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
    protected $format = 'csv';

    /**
     * {@inheritdoc}
     */
    protected function createNormalizer()
    {
        return new FlatJobInstanceNormalizer();
    }

    /**
     * Data provider for testing supportsNormalization method
     * @return array
     */
    public static function getSupportNormalizationData()
    {
        return array(
            array('Oro\Bundle\BatchBundle\Entity\JobInstance', 'json', false),
            array('Oro\Bundle\BatchBundle\Entity\JobInstance', 'csv', true),
            array('stdClass',                                  'json',  false),
            array('stdClass',                                  'csv', false),
        );
    }

    /**
     * Data provider for testing normalize method
     * @return array
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
