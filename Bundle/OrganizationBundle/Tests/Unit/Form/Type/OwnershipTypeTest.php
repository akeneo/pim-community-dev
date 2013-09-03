<?php
namespace Oro\Bundle\OrganizationBundle\Tests\Unit\Form\Type;

use Oro\Bundle\OrganizationBundle\Form\Type\OwnershipType;

class OwnershipTypeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $translator;

    /**
     * @var OwnershipType
     */
    protected $type;

    public function setUp()
    {
        $this->translator = $this->getMockBuilder('Symfony\Component\Translation\TranslatorInterface')
            ->disableOriginalConstructor()
            ->setMethods(array('trans'))
            ->getMockForAbstractClass();

        $this->type = new OwnershipType($this->translator);
    }

    public function tearDown()
    {
        unset($this->translator);
        unset($this->type);
    }

    public function testSetDefaultOptions()
    {
        $optionResolver = $this->getMock('Symfony\Component\OptionsResolver\OptionsResolverInterface');

        $optionResolver->expects($this->once())
            ->method('setDefaults')
            ->with(array('choices' => $this->type->getOwnershipsArray()));
        $this->type->setDefaultOptions($optionResolver);
    }

    public function testGetName()
    {
        $this->assertEquals(OwnershipType::NAME, $this->type->getName());
    }

    public function testGetParent()
    {
        $this->assertEquals('choice', $this->type->getParent());
    }
}
