<?php

namespace Oro\Bundle\TagBundle\Tests\Unit\Form\Type;

use Oro\Bundle\TagBundle\Form\Type\TagSelectType;

class TagSelectTypeTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var TagSelectType
     */
    protected $type;

    protected function setUp()
    {

    }

    public function testSetDefaultOptions()
    {
        $manager = $this->getMockBuilder('Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->getMock();

        $tagManager = $this->getMockBuilder('Oro\Bundle\TagBundle\Entity\TagManager')
            ->disableOriginalConstructor()
            ->getMock();

        $this->type = new TagSelectType($manager, $tagManager);


        $resolver = $this->getMock('Symfony\Component\OptionsResolver\OptionsResolverInterface');
        $resolver->expects($this->once())
            ->method('setDefaults')
            ->with($this->isType('array'));

        $this->type->setDefaultOptions($resolver);
    }

    public function testBuildForm()
    {
        $meta = $this->getMockBuilder('\Doctrine\ORM\Mapping\ClassMetadata')
            ->disableOriginalConstructor()
            ->getMock();

        $manager = $this->getMockBuilder('Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->getMock();
        $manager->expects($this->once())
            ->method('getClassMetadata')
            ->with('Oro\Bundle\TagBundle\Entity\Tag')
            ->will($this->returnValue($meta));

        $tagManager = $this->getMockBuilder('Oro\Bundle\TagBundle\Entity\TagManager')
            ->disableOriginalConstructor()
            ->getMock();

        $this->type = new TagSelectType($manager, $tagManager);


        $modelTransformer = $builder = $this->getMockBuilder('Oro\Bundle\TagBundle\Form\TagsTransformer')
            ->disableOriginalConstructor()
            ->getMock();

        $builder = $this->getMockBuilder('Symfony\Component\Form\FormBuilder')
            ->disableOriginalConstructor()
            ->getMock();
        $builder->expects($this->once())
            ->method('addModelTransformer')
            ->with($this->equalTo($modelTransformer))
            ->will($this->returnSelf());

        $this->type->buildForm($builder, array());
    }
}
