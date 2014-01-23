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
        return [
            ['Oro\Bundle\BatchBundle\Entity\JobInstance', 'json', false],
            ['Oro\Bundle\BatchBundle\Entity\JobInstance', 'xml', false],
            ['Oro\Bundle\BatchBundle\Entity\JobInstance', 'csv', true],
            ['stdClass', 'json', false],
            ['stdClass', 'xml', false],
            ['stdClass', 'csv', false],
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
                    'code'           => 'my_import',
                    'type'           => 'IMPORT',
                    'label'          => 'My import',
                    'connector'      => 'Akeneo',
                    'configuration'  => '[]'
                ],
            ],
        ];
    }
}
