<?php

namespace Oro\Bundle\FormBundle\Tests\Unit\Form\Type;

use Oro\Bundle\FormBundle\Form\Type\MultipleEntityType;
use Symfony\Component\Form\FormView;

class MultipleEntityTypeTypeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var MultipleEntityType
     */
    private $type;

    protected function setUp()
    {
        $em = $this->getMockBuilder('Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->setMethods(array('getClassMetadata', 'getRepository'))
            ->getMockForAbstractClass();

        $this->type = new MultipleEntityType($em);
    }

    public function testGetName()
    {
        $this->assertEquals('oro_multiple_entity', $this->type->getName());
    }

    public function testBuildForm()
    {
        $builder = $this->getMockBuilder('Symfony\Component\Form\FormBuilder')
            ->disableOriginalConstructor()
            ->getMock();
        $builder->expects($this->at(0))
            ->method('add')
            ->with('added', 'oro_entity_identifier', array('class' => '\stdObject', 'multiple' => true))
            ->will($this->returnSelf());
        $builder->expects($this->at(1))
            ->method('add')
            ->with('removed', 'oro_entity_identifier', array('class' => '\stdObject', 'multiple' => true))
            ->will($this->returnSelf());
        $this->type->buildForm($builder, array('class' => '\stdObject', 'extend' => false));
    }

    public function testSetDefaultOptions()
    {
        $optionsResolver = $this->getMockBuilder('Symfony\Component\OptionsResolver\OptionsResolver')
            ->disableOriginalConstructor()
            ->getMock();
        $optionsResolver->expects($this->once())
            ->method('setRequired')
            ->with(array('class'));
        $optionsResolver->expects($this->once())
            ->method('setDefaults')
            ->with(
                array(
                    'class'                 => null,
                    'mapped'                => false,
                    'grid_url'              => null,
                    'default_element'       => null,
                    'initial_elements'      => null,
                    'selector_window_title' => null,
                    'extend'                => false
                )
            );
        $this->type->setDefaultOptions($optionsResolver);
    }

    /**
     * @dataProvider optionsDataProvider
     * @param array  $options
     * @param string $expectedKey
     * @param mixed  $expectedValue
     */
    public function testFinishView($options, $expectedKey, $expectedValue)
    {
        $form = $this->getMockBuilder('Symfony\Component\Form\Form')
            ->disableOriginalConstructor()
            ->getMock();

        $view = new FormView();
        $this->type->finishView($view, $form, $options);
        $this->assertArrayHasKey($expectedKey, $view->vars);
        $this->assertEquals($expectedValue, $view->vars[$expectedKey]);
    }

    public function optionsDataProvider()
    {
        return array(
            array(
                array('grid_url' => '/test'),
                'grid_url',
                '/test'
            ),
            array(
                array(),
                'grid_url',
                null
            ),
            array(
                array('initial_elements' => array()),
                'initial_elements',
                array()
            ),
            array(
                array(),
                'initial_elements',
                null
            ),
            array(
                array('selector_window_title' => 'Select'),
                'selector_window_title',
                'Select'
            ),
            array(
                array(),
                'selector_window_title',
                null
            ),
            array(
                array('default_element' => 'name'),
                'default_element',
                'name'
            ),
            array(
                array(),
                'default_element',
                null
            )
        );
    }
}
