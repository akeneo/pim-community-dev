<?php

namespace Oro\Bundle\FormBundle\Tests\Unit\Form\Type;

use Oro\Bundle\FormBundle\Form\Type\OroJquerySelect2HiddenType;

class OroJquerySelect2HiddenTypeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var OroJquerySelect2HiddenType
     */
    protected $type;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Doctrine\ORM\EntityManager
     */
    protected $em;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Oro\Bundle\FormBundle\EntityAutocomplete\Configuration
     */
    protected $configuration;

    protected function setUp()
    {
        parent::setUp();

        $this->em = $this->getMockBuilder('Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->getMock();
        $this->configuration = $this->getMockBuilder('Oro\Bundle\FormBundle\EntityAutocomplete\Configuration')
            ->disableOriginalConstructor()
            ->getMock();
        $this->type = new OroJquerySelect2HiddenType($this->em, $this->configuration);
    }

    public function testSetDefaultOptions()
    {
        $resolver = $this->getMockBuilder('Symfony\Component\OptionsResolver\OptionsResolver')
            ->disableOriginalConstructor()
            ->getMock();
        $resolver->expects($this->once())
            ->method('setNormalizers')
            ->with($this->isType('array'))
            ->will($this->returnSelf());

        $resolver->expects($this->once())
            ->method('setDefaults')
            ->with(
                array(
                    'empty_value' => '',
                    'empty_data' => null,
                    'data_class' => null,
                    'autocomplete_transformer' => null,
                    'configs' => array(
                        'allowClear'         => true,
                        'minimumInputLength' => 1,
                    )
                )
            )
            ->will($this->returnSelf());

        $this->type->setDefaultOptions($resolver);
    }

    public function testBuildFormAutocompleteOptions()
    {
        $this->configuration->expects($this->once())
            ->method('getAutocompleteOptions')
            ->with('test')
            ->will($this->returnValue(array('entity_class' => 'TestBundle:Test')));

        $builder = $this->assertEntityToIdTransformer();
        $this->type->buildForm($builder, array('autocomplete_alias' => 'test'));
    }

    public function testBuildFormEntityClass()
    {
        $builder = $this->assertEntityToIdTransformer();
        $this->type->buildForm($builder, array('entity_class' => 'test'));
    }

    public function assertEntityToIdTransformer()
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
        return $builder;
    }

    /**
     * @expectedException Symfony\Component\OptionsResolver\Exception\MissingOptionsException
     * @expectedExceptionMessage Option "autocomplete_alias" or "entity_class" must be defined.
     */
    public function testBuildFormException()
    {
        $builder = $this->getMockBuilder('Symfony\Component\Form\FormBuilder')
            ->disableOriginalConstructor()
            ->getMock();
        $this->type->buildForm($builder, array());
    }

    /**
     * @dataProvider autocompleteOptionsDataProvider
     * @param array $autocompleteOptions
     * @param array $expected
     */
    public function testBuildViewAutocompleteOptions($autocompleteOptions, $expected)
    {
        $options = array('autocomplete_alias' => 'test');

        $this->configuration->expects($this->once())
            ->method('getAutocompleteOptions')
            ->with('test')
            ->will($this->returnValue($autocompleteOptions));

        $view = $this->getMockBuilder('Symfony\Component\Form\FormView')
            ->disableOriginalConstructor()
            ->getMock();
        $form = $this->getMockBuilder('Symfony\Component\Form\Form')
            ->disableOriginalConstructor()
            ->setMethods(array('getData'))
            ->getMock();
        $form->expects($this->once())
            ->method('getData')
            ->will($this->returnValue(null));

        $this->type->buildView($view, $form, $options);

        $this->assertEquals($expected, $view->vars);
    }

    public function autocompleteOptionsDataProvider()
    {
        return array(
            'simple' => array(
                array(
                    'route' => 'test_route',
                    'properties' => array($this->getPropertyMock('property1')),
                ),
                array(
                    'value' => null, 'attr' => array(),
                    'configs' => array(
                        'route' => 'test_route',
                        'properties' => array('property1'),
                        'autocomplete_alias' => 'test',
                        'minimumInputLength' => 1,
                        'allowClear' => true
                    )
                )
            ),
            'default values reset' => array(
                array(
                    'route' => 'test_route',
                    'properties' => array($this->getPropertyMock('property1')),
                    'form_options' => array(
                        'minimumInputLength' => 10,
                        'allowClear' => false
                    )
                ),
                array(
                    'value' => null, 'attr' => array(),
                    'configs' => array(
                        'route' => 'test_route',
                        'properties' => array('property1'),
                        'autocomplete_alias' => 'test',
                        'minimumInputLength' => 10,
                        'allowClear' => false
                    )
                )
            ),
            'no route' => array(
                array(
                    'properties' => array($this->getPropertyMock('property1')),
                    'url' => '/test'
                ),
                array(
                    'value' => null, 'attr' => array(),
                    'configs' => array(
                        'ajax' => array(
                            'url' => '/test'
                        ),
                        'properties' => array('property1'),
                        'autocomplete_alias' => 'test',
                        'minimumInputLength' => 1,
                        'allowClear' => true
                    )
                )
            ),
            'no route with ajax properties' => array(
                array(
                    'properties' => array($this->getPropertyMock('property1')),
                    'url' => '/test',
                    'form_options' => array(
                        'ajax' => array(
                            'type' => 'jsonp'
                        )
                    )
                ),
                array(
                    'value' => null, 'attr' => array(),
                    'configs' => array(
                        'ajax' => array(
                            'url' => '/test',
                            'type' => 'jsonp'
                        ),
                        'properties' => array('property1'),
                        'autocomplete_alias' => 'test',
                        'minimumInputLength' => 1,
                        'allowClear' => true
                    )
                )
            ),
        );
    }

    /**
     * @dataProvider configsOptionsDataProvider
     * @param array $options
     * @param array $expected
     */
    public function testBuildViewWithConfigs($options, $expected)
    {
        $view = $this->getMockBuilder('Symfony\Component\Form\FormView')
            ->disableOriginalConstructor()
            ->getMock();
        $form = $this->getMockBuilder('Symfony\Component\Form\Form')
            ->disableOriginalConstructor()
            ->setMethods(array('getData'))
            ->getMock();
        $form->expects($this->once())
            ->method('getData')
            ->will($this->returnValue(null));

        $this->type->buildView($view, $form, $options);

        $this->assertEquals($expected, $view->vars);
    }

    public function configsOptionsDataProvider()
    {
        return array(
            array(
                array('configs' => array('properties' => array('property1'))),
                array(
                    'value' => null, 'attr' => array(),
                    'configs' => array(
                        'properties' => array('property1'),
                        'minimumInputLength' => 1,
                        'allowClear' => true
                    )
                )
            ),
            array(
                array('configs' => array('properties' => 'property1')),
                array(
                    'value' => null, 'attr' => array(),
                    'configs' => array(
                        'properties' => array('property1'),
                        'minimumInputLength' => 1,
                        'allowClear' => true
                    )
                )
            )
        );
    }

    /**
     * @expectedException \Symfony\Component\OptionsResolver\Exception\MissingOptionsException
     * @expectedExceptionMessage Missing required "configs.properties" option
     */
    public function testBuildViewException()
    {
        $options = array();

        $view = $this->getMockBuilder('Symfony\Component\Form\FormView')
            ->disableOriginalConstructor()
            ->getMock();
        $form = $this->getMockBuilder('Symfony\Component\Form\Form')
            ->disableOriginalConstructor()
            ->setMethods(array('getData'))
            ->getMock();
        $form->expects($this->never())
            ->method('getData');

        $this->type->buildView($view, $form, $options);
    }

    public function testBuildViewEntityEncoding()
    {
        $options = array('configs' => array('properties' => 'property1'), 'autocomplete_transformer' => null);

        $view = $this->getMockBuilder('Symfony\Component\Form\FormView')
            ->disableOriginalConstructor()
            ->getMock();
        $form = $this->getMockBuilder('Symfony\Component\Form\Form')
            ->disableOriginalConstructor()
            ->setMethods(array('getData'))
            ->getMock();
        $form->expects($this->exactly(2))
            ->method('getData')
            ->will($this->returnValue(array('id' => 10, 'property1' => 'value1', 'property2' => 'value2')));

        $this->type->buildView($view, $form, $options);

        $expected = array(
            'value' => null,
            'attr' => array(
                'data-entity' => '{"id":10,"property1":"value1"}'
            ),
            'configs' => array(
                'properties' => array('property1'),
                'minimumInputLength' => 1,
                'allowClear' => true
            )
        );
        $this->assertEquals($expected, $view->vars);
    }

    protected function getPropertyMock($name)
    {
        $mock = $this->getMockBuilder('Oro\Bundle\FormBundle\EntityAutocomplete\Property')
            ->disableOriginalConstructor()
            ->getMock();
        $mock->expects($this->any())
            ->method('getName')
            ->will($this->returnValue($name));
        return $mock;
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
