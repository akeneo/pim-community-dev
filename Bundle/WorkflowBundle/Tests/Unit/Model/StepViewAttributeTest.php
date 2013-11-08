<?php

namespace Oro\Bundle\WorkflowBundle\Tests\Unit\Model;

use Oro\Bundle\WorkflowBundle\Model\StepViewAttribute;

class StepViewAttributeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var StepViewAttribute
     */
    protected $attribute;

    public function setUp()
    {
        $this->attribute = new StepViewAttribute();
    }

    public function testConstructor()
    {
        $options = array('bar' => 'baz');

        $attribute = new StepViewAttribute($options);
        $this->assertEquals($options, $attribute->getOptions());
    }

    public function testSetGetOptions()
    {
        $options = array('bar' => 'baz');
        $this->attribute->setOptions($options);
        $this->assertEquals($options, $this->attribute->getOptions());
    }

    public function testGetOption()
    {
        $options = array('bar' => 'baz');
        $this->attribute->setOptions($options);
        $this->assertEquals('baz', $this->attribute->getOption('bar'));
        $this->assertEquals('bar', $this->attribute->getOption('foo', 'bar'));
    }
}
