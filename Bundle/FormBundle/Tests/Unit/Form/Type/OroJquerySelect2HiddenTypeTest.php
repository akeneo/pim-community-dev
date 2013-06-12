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
            ->method('setRequired')
            ->with(array('autocomplete_alias'))
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
        $this->configuration->expects($this->once())
            ->method('getAutocompleteOptions')
            ->with('test')
            ->will($this->returnValue(array('entity_class' => 'TestBundle:Test')));

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
        $this->type->buildForm($builder, array('autocomplete_alias' => 'test'));
    }

    /**
     * @dataProvider optionsDataProvider
     * @param array $data
     * @param array $autocompleteOptions
     * @param array $expectedConfigs
     * @param string $expectedEncodedData
     */
    public function testBuildView(array $data, array $autocompleteOptions, array $expectedConfigs, $expectedEncodedData)
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
        $form->expects($this->once())->method('getData')->will($this->returnValue($data));
        $this->type->buildView($view, $form, $options);

        $this->assertInternalType('array', $view->vars);
        $this->assertArrayHasKey('attr', $view->vars);
        $this->assertInternalType('array', $view->vars['attr']);
        $this->assertArrayHasKey('encoded-data', $view->vars['attr']);
        $this->assertEquals($expectedEncodedData, $view->vars['attr']['encoded-data']);
        $this->assertArrayHasKey('configs', $view->vars);
        $this->assertEquals($expectedConfigs, $view->vars['configs']);
    }

    public function optionsDataProvider()
    {
        return array(
            array(
                array('property' => 'Test Value'),
                array(
                    'route' => 'test_route',
                    'properties' => array($this->getPropertyMock('property')),
                    'url' => '/test',
                    'form_options' => array('ajax' => array('type' => 'jsonp'))
                ),
                array(
                    'route' => 'test_route',
                    'properties' => array('property'),
                    'autocomplete_alias' => 'test',
                    'ajax' => array(
                        'url' => '/test',
                        'type' => 'jsonp'
                    )
                ),
                json_encode(array('id' => null, 'property' => 'Test Value'))
            )
        );
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
