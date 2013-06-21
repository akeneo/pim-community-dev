<?php
namespace Oro\Bundle\AddressBundle\Tests\Unit\Type;

use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Oro\Bundle\AddressBundle\Form\Type\RegionType;
use Oro\Bundle\FormBundle\Form\Type\TranslatableEntityType;

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
        /** @var OptionsResolverInterface $resolver */
        $resolver = $this->getMock('Symfony\Component\OptionsResolver\OptionsResolverInterface');
        $resolver->expects($this->once())
            ->method('setDefaults')
            ->with($this->isType('array'));
        $this->type->setDefaultOptions($resolver);
    }

    public function testGetParent()
    {
        $this->assertEquals(TranslatableEntityType::NAME, $this->type->getParent());
    }

    public function testGetName()
    {
        $this->assertEquals('oro_region', $this->type->getName());
    }

    public function testBuildForm()
    {
        $builderMock = $this->getMock('Symfony\Component\Form\Tests\FormBuilderInterface');
        $options = array(RegionType::COUNTRY_OPTION_KEY => 'test');

        $builderMock->expects($this->once())
            ->method('setAttribute')
            ->with($this->equalTo(RegionType::COUNTRY_OPTION_KEY), $this->equalTo('test'));


        $this->type->buildForm($builderMock, $options);
    }

    public function testFinishView()
    {
        $formViewMock = $this->getMock('Symfony\Component\Form\FormView');
        $formMock = $this->getMockBuilder('Symfony\Component\Form\Form')
            ->disableOriginalConstructor()
            ->getMock();
        $formMock->expects($this->once())
            ->method('getAttribute')
            ->with($this->equalTo(RegionType::COUNTRY_OPTION_KEY))
            ->will($this->returnValue(''));

        $this->type->finishView($formViewMock, $formMock, array());
    }
}
