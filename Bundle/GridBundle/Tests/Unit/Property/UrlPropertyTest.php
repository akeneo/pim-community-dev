<?php

namespace Oro\Bundle\GridBundle\Tests\Unit\Property;

use Symfony\Bundle\FrameworkBundle\Routing\Router;

use Oro\Bundle\GridBundle\Datagrid\ResultRecord;
use Oro\Bundle\GridBundle\Datagrid\ResultRecordInterface;
use Oro\Bundle\GridBundle\Property\UrlProperty;

class UrlPropertyTest extends \PHPUnit_Framework_TestCase
{
    const TEST_PROPERTY_NAME = 'property_name';
    const TEST_ROUTE_NAME = 'route_name';

    /**
     * @var Router|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $router;

    protected function setUp()
    {
        $this->router = $this->getMockBuilder('Symfony\Bundle\FrameworkBundle\Routing\Router')
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * @param array $placeholders
     * @param bool $isAbsolute
     * @param string $anchor
     * @return UrlProperty
     */
    protected function createUrlProperty(
        array $placeholders = array(),
        $isAbsolute = false,
        $anchor = null
    ) {
        return new UrlProperty(
            self::TEST_PROPERTY_NAME,
            $this->router,
            self::TEST_ROUTE_NAME,
            $placeholders,
            $isAbsolute,
            $anchor
        );
    }

    public function testGetName()
    {
        $property = $this->createUrlProperty();
        $this->assertEquals(self::TEST_PROPERTY_NAME, $property->getName());
    }

    /**
     * @dataProvider getValueDataProvider
     */
    public function testGetValue(
        $expectedParameters,
        ResultRecordInterface $record,
        $placeholders = array(),
        $isAbsolute = false,
        $anchor = null
    ) {
        $expectedRoute = 'test';

        $property = $this->createUrlProperty($placeholders, $isAbsolute, $anchor);

        $this->router->expects($this->once())
            ->method('generate')
            ->with(self::TEST_ROUTE_NAME, $expectedParameters, $isAbsolute)
            ->will($this->returnValue($expectedRoute));

        $this->assertEquals($expectedRoute.$anchor, $property->getValue($record));
    }

    /**
     * @return array
     */
    public function getValueDataProvider()
    {
        return array(
            'no route parameters' => array(
                'expectedParameters' => array(),
                'data' => new ResultRecord(array())
            ),
            'has placeholders' => array(
                'expectedParameters' => array(
                    'id' => 1
                ),
                'data' =>
                    new ResultRecord(
                        array(
                            'id' => 1,
                            'name' => 'Test name',
                        )
                    ),
                'placeholders' => array(
                    'id'
                ),
                'isAbsolute' => true
            ),
            'has placeholders as associated array' => array(
                'expectedParameters' => array(
                    'id' => 1
                ),
                'data' => new ResultRecord(
                    array(
                        '_id_' => 1,
                        'name' => 'Test name',
                    )
                ),
                'placeholders' => array(
                    'id' => '_id_'
                )
            ),
            'has placeholders and anchor' => array(
                'expectedParameters' => array(
                    'id' => 1
                ),
                'data' =>
                    new ResultRecord(
                        array(
                            'id' => 1,
                            'name' => 'Test name',
                        )
                    ),
                'placeholders' => array(
                    'id'
                ),
                'isAbsolute' => true,
                'anchor' => '#myAnchor'
            ),
        );
    }
}
