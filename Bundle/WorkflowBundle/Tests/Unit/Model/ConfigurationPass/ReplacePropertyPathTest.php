<?php

namespace Oro\Bundle\WorkflowBundle\Tests\Unit\Model\ConfigurationPass;

use Symfony\Component\PropertyAccess\PropertyPath;

use Oro\Bundle\WorkflowBundle\Model\ConfigurationPass\ReplacePropertyPath;

class ReplacePropertyPathTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @param array $sourceData
     * @param array $expectedData
     * @param string $prefix
     *
     * @dataProvider passDataProvider
     */
    public function testPassConfiguration(array $sourceData, array $expectedData, $prefix = null)
    {
        $parameterPass = new ReplacePropertyPath($prefix);
        $actualData = $parameterPass->passConfiguration($sourceData);

        $this->assertEquals($expectedData, $this->replacePropertyPathsWithElements($actualData));
    }

    /**
     * @param array $data
     * @return array
     */
    protected function replacePropertyPathsWithElements($data)
    {
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $data[$key] = $this->replacePropertyPathsWithElements($value);
            } elseif ($value instanceof PropertyPath) {
                $data[$key] = $value->getElements();
            }
        }

        return $data;
    }

    /**
     * @return array
     */
    public function passDataProvider()
    {
        return array(
            'empty data' => array(
                'sourceData' => array(),
                'expectedData' => array()
            ),
            'data with paths' => array(
                'sourceData' => array(
                    'a' => '$path.component',
                    'b' => array(
                        'c' => '$another.path.component'
                    )
                ),
                'expectedData' => array(
                    'a' => array('path', 'component'),
                    'b' => array(
                        'c' => array('another', 'path', 'component'),
                    )
                )
            ),
            'data with prefix' => array(
                'sourceData' => array(
                    'a' => '$path.component',
                    'b' => array(
                        'c' => '$another.path.component'
                    )
                ),
                'expectedData' => array(
                    'a' => array('prefix', 'path', 'component'),
                    'b' => array(
                        'c' => array('prefix', 'another', 'path', 'component'),
                    )
                ),
                'prefix' => 'prefix'
            ),
            'data with root ignore prefix' => array(
                'sourceData' => array(
                    'a' => '$.path.component',
                    'b' => array(
                        'c' => '$.another.path.component'
                    )
                ),
                'expectedData' => array(
                    'a' => array('path', 'component'),
                    'b' => array(
                        'c' => array('another', 'path', 'component'),
                    )
                ),
                'prefix' => 'prefix'
            ),
        );
    }
}
