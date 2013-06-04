<?php

namespace Oro\Bundle\FormBundle\Tests\Unit\Form\Type;

use Oro\Bundle\FormBundle\Form\Type\OroJquerySelect2HiddenType;

class OroJquerySelect2HiddenTypeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var OroJquerySelect2HiddenType
     */
    protected $type;

    protected $em;

    protected $entityTransformer;

    protected function setUp()
    {
        parent::setUp();

        $this->entityTransformer = $this->getMockBuilder('Oro\Bundle\FormBundle\EntityAutocomplete\Transformer\EntityTransformerInterface')
            ->getMock();
        $this->em = $this->getMockBuilder('Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->getMock();
        $this->type = new OroJquerySelect2HiddenType($this->entityTransformer, $this->em);
    }

    public function testSetDefaultOptions()
    {
        $resolver = $this->getMockBuilder('Symfony\Component\OptionsResolver\OptionsResolver')
            ->disableOriginalConstructor()
            ->getMock();
        $resolver->expects($this->once())
            ->method('setRequired')
            ->with(array('autocomplete_alias', 'class'))
            ->will($this->returnSelf());

        $resolver->expects($this->once())
            ->method('setDefaults')
            ->with(
                array(
                    'empty_value' => '',
                    'empty_data' => null,
                    'data_class' => null
                )
            )
            ->will($this->returnSelf());

        $this->type->setDefaultOptions($resolver);
    }

    public function testBuildForm()
    {
        $builder = $this->getMockBuilder('Symfony\Component\Form\FormBuilder')
            ->disableOriginalConstructor()
            ->getMock();
        $builder->expects($this->once())
            ->method('addModelTransformer')
            ->with($this->isInstanceOf('Oro\Bundle\FormBundle\Form\DataTransformer\EntityToIdTransformer'))
            ->will($this->returnSelf());

        $metadata = $this->getMockBuilder('\Doctrine\ORM\Mapping\ClassMetadata')
            ->disableOriginalConstructor()
            ->getMock();
        $metadata->expects($this->once())
            ->method('getSingleIdentifierFieldName')
            ->will($this->returnValue('id'));
        $this->em->expects($this->once())
            ->method('getClassMetadata')
            ->will($this->returnValue($metadata));
        $this->type->buildForm($builder, array('class' => 'TestBundle:Test'));
    }

    public function testBuildView()
    {
        $data = null;
        $options = array('autocomplete_alias' => 'test');
        $title = 'Test Value';
        $view = $this->getMockBuilder('Symfony\Component\Form\FormView')
            ->disableOriginalConstructor()
            ->getMock();
        $form = $this->getMockBuilder('Symfony\Component\Form\Form')
            ->disableOriginalConstructor()
            ->getMock();
        $this->entityTransformer->expects($this->once())
            ->method('transform')
            ->with($options['autocomplete_alias'], $data)
            ->will($this->returnValue($title));
        $this->type->buildView($view, $form, $options);

        $this->assertInternalType('array', $view->vars);
        $this->assertArrayHasKey('attr', $view->vars);
        $this->assertInternalType('array', $view->vars['attr']);
        $this->assertArrayHasKey('data-title', $view->vars['attr']);
        $this->assertEquals($title, $view->vars['attr']['data-title']);
    }

    public function testGetName()
    {
        $this->assertEquals('oro_jqueryselect2_hidden', $this->type->getName());
    }

    public function testGetParent()
    {
        $this->assertEquals('genemu_jqueryselect2_hidden', $this->type->getParent());
    }
}
