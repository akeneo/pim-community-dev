<?php

namespace Oro\Bundle\AddressBundle\Tests\Unit\Type;

use Symfony\Component\Form\FormView;

use Oro\Bundle\AddressBundle\Form\Type\RegionType;

class RegionTypeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var RegionType
     */
    protected $type;

    /**
     * Setup test env
     */
    public function setUp()
    {
        $this->type = new RegionType(
            'Oro\Bundle\AddressBundle\Entity\Address',
            'Oro\Bundle\AddressBundle\Entity\Value\AddressValue'
        );
    }

    public function testSetDefaultOptions()
    {
        $resolver = $this->getMock('Symfony\Component\OptionsResolver\OptionsResolverInterface');
        $resolver->expects($this->once())
            ->method('setDefaults')
            ->with($this->isType('array'));
        $this->type->setDefaultOptions($resolver);
    }

    public function testGetParent()
    {
        $this->assertEquals('genemu_jqueryselect2_translatable_entity', $this->type->getParent());
    }

    public function testGetName()
    {
        $this->assertEquals('oro_region', $this->type->getName());
    }

    public function testBuildForm()
    {
        $builderMock = $this->getMock('Symfony\Component\Form\Test\FormBuilderInterface');
        $options = array(RegionType::COUNTRY_OPTION_KEY => 'test');

        $builderMock->expects($this->once())
            ->method('setAttribute')
            ->with($this->equalTo(RegionType::COUNTRY_OPTION_KEY), $this->equalTo('test'));


        $this->type->buildForm($builderMock, $options);
    }

    public function testFinishView()
    {
        $optionKey = 'countryFieldName';

        $formConfigMock = $this->getMock('Symfony\Component\Form\FormConfigInterface');
        $formConfigMock->expects($this->once())
            ->method('getAttribute')
            ->with($this->equalTo(RegionType::COUNTRY_OPTION_KEY))
            ->will($this->returnValue($optionKey));

        $formMock = $this->getMockBuilder('Symfony\Component\Form\Form')
            ->disableOriginalConstructor()
            ->setMethods(array('getConfig'))
            ->getMock();
        $formMock->expects($this->once())
            ->method('getConfig')
            ->will($this->returnValue($formConfigMock));

        $formView = new FormView();
        $this->type->finishView($formView, $formMock, array());
        $this->assertArrayHasKey('country_field', $formView->vars);
        $this->assertEquals($optionKey, $formView->vars['country_field']);
    }
}
