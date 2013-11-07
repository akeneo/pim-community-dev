<?php

namespace Oro\Bundle\FormBundle\Tests\Unit\Autocomplete;

use Oro\Bundle\FormBundle\Autocomplete\FullNameSearchHandler;

class FullNameSearchHandlerTest extends \PHPUnit_Framework_TestCase
{
    const TEST_ENTITY_CLASS = 'FooEntityClass';

    /**
     * @var array
     */
    protected $testProperties = array('name', 'email');

    /**
     * @var FullNameSearchHandler
     */
    protected $searchHandler;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $nameFormatter;

    protected function setUp()
    {
        $this->nameFormatter = $this->getMockBuilder('Oro\Bundle\LocaleBundle\Formatter\NameFormatter')
            ->disableOriginalConstructor()
            ->getMock();
        $this->searchHandler = new FullNameSearchHandler(self::TEST_ENTITY_CLASS, $this->testProperties);
    }

    public function testConvertItem()
    {
        $fullName = 'Mr. John Doe';

        $entity = new \stdClass();
        $entity->name = 'John';
        $entity->email = 'john@example.com';

        $this->nameFormatter->expects($this->once())
            ->method('format')
            ->with($entity)
            ->will($this->returnValue($fullName));

        $this->searchHandler->setNameFormatter($this->nameFormatter);
        $this->assertEquals(
            array(
                'name' => 'John',
                'email' => 'john@example.com',
                'fullName' => $fullName,
            ),
            $this->searchHandler->convertItem($entity)
        );
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Name formatter must be configured
     */
    public function testConvertItemFails()
    {
        $this->searchHandler->convertItem(new \stdClass());
    }
}
