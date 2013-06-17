<?php

namespace Oro\Bundle\FormBundle\Tests\Unit\EntityAutocomplete;

use Oro\Bundle\FormBundle\EntityAutocomplete\SearchFactoryInterface;
use Oro\Bundle\FormBundle\EntityAutocomplete\CompositeSearchFactory;
use Oro\Bundle\FormBundle\Tests\Unit\MockHelper;

class CompositeSearchFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var SearchFactoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $childFactory;

    /**
     * @var CompositeSearchFactory
     */
    protected $factory;

    protected function setUp()
    {
        $this->childFactory = $this->getMock('Oro\Bundle\FormBundle\EntityAutocomplete\SearchFactoryInterface');
        $this->factory = new CompositeSearchFactory(array('foo_type' => $this->childFactory));
    }

    public function testCreate()
    {
        $options = array('type' => 'foo_type');

        $searchHandler = $this->getMock('Oro\Bundle\FormBundle\EntityAutocomplete\SearchHandlerInterface');

        $this->childFactory->expects($this->once())
            ->method('create')
            ->with($options)
            ->will($this->returnValue($searchHandler));

        $this->assertSame($searchHandler, $this->factory->create($options));
    }

    /**
     * @dataProvider createFailsDataProvider
     * @param array $options
     * @param string $expectedException
     * @param string $expectedExceptionMessage
     */
    public function testCreateFails(
        array $options,
        $expectedException,
        $expectedExceptionMessage
    ) {
        $this->setExpectedException($expectedException, $expectedExceptionMessage);
        $this->factory->create($options);
    }

    /**
     * @return array
     */
    public function createFailsDataProvider()
    {
        return array(
            'properties required' => array(
                'options' => array(),
                'RuntimeException',
                'Option "type" is required'
            ),
            'service not valid' => array(
                'options' => array(
                    'type' => 'unknown_type'
                ),
                'RuntimeException',
                'Autocomplete search factory for type "unknown_type" is not registered'
            )
        );
    }
}
