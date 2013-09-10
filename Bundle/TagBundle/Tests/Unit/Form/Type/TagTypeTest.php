<?php

namespace Oro\Bundle\TagBundle\Tests\Unit\Form\Type;

use Oro\Bundle\TagBundle\Form\Type\TagType;

class TagTypeTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var TagType
     */
    protected $type;

    protected function setUp()
    {
        $this->type = new TagType();
    }

    public function tearDown()
    {
        unset($this->type);
    }

    public function testSetDefaultOptions()
    {
        $resolver = $this->getMock('Symfony\Component\OptionsResolver\OptionsResolverInterface');
        $resolver->expects($this->once())
            ->method('setDefaults')
            ->with($this->isType('array'));

        $this->type->setDefaultOptions($resolver);
    }

    public function testGetName()
    {
        $this->assertEquals('oro_tag_tag', $this->type->getName());
    }

    public function testBuildForm()
    {
        $builder = $this->getMockBuilder('Symfony\Component\Form\FormBuilder')
            ->disableOriginalConstructor()
            ->getMock();
        $builder->expects($this->once())
            ->method('add')
            ->will($this->returnSelf());

        $this->type->buildForm($builder, array());
    }
}
