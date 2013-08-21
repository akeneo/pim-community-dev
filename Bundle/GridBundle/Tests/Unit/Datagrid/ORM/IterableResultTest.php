<?php

namespace Oro\Bundle\GridBundle\Tests\Unit\Datagrid\ORM;

use Oro\Bundle\GridBundle\Datagrid\ORM\IterableResult;

class IterableResultTest extends \PHPUnit_Framework_TestCase
{
    /** @var IterableResult */
    protected $iterator;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $proxyQuery;

    public function setUp()
    {
        $this->proxyQuery = $this->getMockBuilder('Oro\Bundle\GridBundle\Datagrid\ORM\ProxyQuery')
            ->disableOriginalConstructor()->disableOriginalClone()->getMock();
    }

    public function tearDown()
    {
        unset($this->proxyQuery);
    }

    /**
     * @dataProvider bufferSizeProvider
     */
    public function testSetBufferSize($size, $queryMaxSize, $expectedException, $expectedExceptionMessage, $finalSize)
    {
        if ($expectedException) {
            $this->setExpectedException($expectedException, $expectedExceptionMessage);
        }

        $this->proxyQuery->expects($this->any())->method('getMaxResults')->will($this->returnValue($queryMaxSize));

        $this->iterator = new IterableResult($this->proxyQuery);
        $this->iterator->setBufferSize($size);
        $this->assertAttributeEquals($finalSize, 'pageSize', $this->iterator);
    }

    /**
     * @return array
     */
    public function bufferSizeProvider()
    {
        return array(
            'bad max size' => array(
                -1,
                10,
                '\InvalidArgumentException',
                '$pageSize must be greater than 0',
                -1
            ),
            'correct size' => array(
                10,
                10,
                false,
                false,
                10
            ),
            'size is bigger than maxQuerySize' => array(
                20,
                10,
                false,
                false,
                10
            ),
        );
    }
}
