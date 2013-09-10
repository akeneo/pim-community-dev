<?php

namespace Oro\Bundle\TagBundle\Tests\Unit\Form\Type;

use Oro\Bundle\TagBundle\Form\Type\TagSelectType;

class TagSelectTypeTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var TagSelectType
     */
    protected $type;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $transformer;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $subscriber;

    public function setUp()
    {
        $this->transformer = $this->getMockBuilder('Oro\Bundle\TagBundle\Form\Transformer\TagTransformer')
            ->disableOriginalConstructor()
            ->getMock();

        $this->subscriber = $this->getMockBuilder('Oro\Bundle\TagBundle\Form\EventSubscriber\TagSubscriber')
            ->disableOriginalConstructor()
            ->getMock();

        $this->type = new TagSelectType($this->subscriber, $this->transformer);
    }

    public function tearDown()
    {
        unset($this->transformer);
        unset($this->subscriber);
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

    public function testBuildForm()
    {
        $builder = $this->getMockBuilder('Symfony\Component\Form\FormBuilder')
            ->disableOriginalConstructor()
            ->getMock();
        $builder->expects($this->once())
            ->method('addEventSubscriber')
            ->with($this->subscriber)
            ->will($this->returnSelf());

        $builder->expects($this->at(1))
            ->method('add')
            ->with('autocomplete', 'oro_tag_autocomplete')
            ->will($this->returnSelf());

        $builder->expects($this->any())
            ->method('add')
            ->will($this->returnSelf());

        $builder->expects($this->any())
            ->method('create')
            ->will($this->returnSelf());

        $builder->expects($this->exactly(2))
            ->method('addViewTransformer')
            ->with($this->transformer)
            ->will($this->returnSelf());

        $this->type->buildForm($builder, array());
    }

    public function testGetName()
    {
        $this->assertEquals('oro_tag_select', $this->type->getName());
    }
}
