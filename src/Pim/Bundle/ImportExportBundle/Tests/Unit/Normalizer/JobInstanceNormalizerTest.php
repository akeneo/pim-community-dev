<?php

namespace Pim\Bundle\ImportExportBundle\Tests\Unit\Normalizer;

use Oro\Bundle\BatchBundle\Entity\JobInstance;
use Pim\Bundle\ImportExportBundle\Normalizer\JobInstanceNormalizer;

/**
 * Test related class
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class JobInstanceNormalizerTest extends NormalizerTestCase
{
    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->normalizer = new JobInstanceNormalizer();
        $this->format     = 'json';
    }

    /**
     * {@inheritdoc}
     */
    public static function getSupportNormalizationData()
    {
        return [
            ['Oro\Bundle\BatchBundle\Entity\JobInstance', 'json', true],
            ['Oro\Bundle\BatchBundle\Entity\JobInstance', 'xml', true],
            ['Oro\Bundle\BatchBundle\Entity\JobInstance', 'csv', false],
            ['stdClass', 'json', false],
            ['stdClass', 'xml', false],
            ['stdClass', 'csv', false],
        ];
    }

    /**
     * {@inheritdoc}
     *
     * @dataProvider getSupportNormalizationData
     */
    public function testSupportNormalization($class, $format, $isSupported)
    {
        $data = $this->getMockBuilder($class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->assertSame($isSupported, $this->normalizer->supportsNormalization($data, $format));
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
                    'configuration'  => []
                ],
            ],
        ];
    }

    /**
     * {@inheritdoc}
     *
     * @return JobInstance
     */
    protected function createEntity(array $data)
    {
        $job = new JobInstance($data['connector'], $data['type'], 'alias');
        $job->setCode($data['code']);
        $job->setLabel($data['label']);

        return $job;
    }
}
