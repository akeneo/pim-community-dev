<?php
namespace Oro\Bundle\OrganizationBundle\Tests\Unit\Form\Type;

use Oro\Bundle\OrganizationBundle\Form\Type\BusinessUnitType;

class BusinessUnitTypeTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var BusinessUnitType
     */
    protected $form;

    protected function setUp()
    {
        $this->form = new BusinessUnitType();
    }

    public function testSetDefaultOptions()
    {
        $optionResolver = $this->getMock('Symfony\Component\OptionsResolver\OptionsResolverInterface');

        $optionResolver->expects($this->once())
            ->method('setDefaults')
            ->with(array('data_class' => 'Oro\Bundle\OrganizationBundle\Entity\BusinessUnit'));
        $this->form->setDefaultOptions($optionResolver);
    }

    public function testBuildForm()
    {
        $builder = $this->getMockBuilder('Symfony\Component\Form\FormBuilder')
            ->disableOriginalConstructor()
            ->getMock();
        $builder->expects($this->exactly(8))
            ->method('add')
            ->will($this->returnSelf());

        $this->form->buildForm($builder, array());
    }

    public function testGetName()
    {
        $this->assertEquals('oro_business_unit', $this->form->getName());
    }
}
