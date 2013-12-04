<?php

namespace Oro\Bundle\AddressBundle\Tests\Unit\Validator\Constraints;

use Oro\Bundle\AddressBundle\Entity\AddressType;
use Oro\Bundle\AddressBundle\Validator\Constraints\UniqueAddressTypesValidator;

class UniqueAddressTypesValidatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException \Symfony\Component\Validator\Exception\UnexpectedTypeException
     * @expectedExceptionMessage Expected argument of type array or Traversable and ArrayAccess, boolean given
     */
    public function testValidateExceptionWhenInvalidArgumentType()
    {
        $constraint = $this->getMock('Symfony\Component\Validator\Constraint');
        $validator = new UniqueAddressTypesValidator();
        $validator->validate(false, $constraint);
    }

    //@codingStandardsIgnoreStart
    /**
     * @expectedException \Symfony\Component\Validator\Exception\ValidatorException
     * @expectedExceptionMessage Expected argument of type Oro\Bundle\AddressBundle\Entity\AbstractTypedAddress, array given
     */
    //@codingStandardsIgnoreEnd
    public function testValidateExceptionWhenInvalidArgumentElementType()
    {
        $constraint = $this->getMock('Symfony\Component\Validator\Constraint');
        $validator = new UniqueAddressTypesValidator();
        $validator->validate(array(1), $constraint);
    }

    /**
     * @dataProvider validAddressesDataProvider
     * @param array $addresses
     */
    public function testValidateValid(array $addresses)
    {
        $context = $this->getMockBuilder('Symfony\Component\Validator\ExecutionContext')
            ->disableOriginalConstructor()
            ->getMock();
        $context->expects($this->never())
            ->method('addViolation');

        $constraint = $this->getMock('Oro\Bundle\AddressBundle\Validator\Constraints\UniqueAddressTypes');
        $validator = new UniqueAddressTypesValidator();
        $validator->initialize($context);

        $validator->validate($addresses, $constraint);
    }

    /**
     * @return array
     */
    public function validAddressesDataProvider()
    {
        return array(
            'no addresses' => array(
                array()
            ),
            'one address without type' => array(
                array($this->getTypedAddressMock(array()))
            ),
            'one address with type' => array(
                array($this->getTypedAddressMock(array('billing' => 'billing label')))
            ),
            'many addresses unique types' => array(
                array(
                    $this->getTypedAddressMock(array('billing' => 'billing label')),
                    $this->getTypedAddressMock(array('shipping' => 'shipping label')),
                    $this->getTypedAddressMock(array('billing_corporate' => 'billing_corporate label')),
                    $this->getTypedAddressMock(array()),
                )
            ),
            'empty address' => array(
                array(
                    $this->getTypedAddressMock(array('billing' => 'billing label')),
                    $this->getTypedAddressMock(array('shipping' => 'shipping label')),
                    $this->getTypedAddressMock(array(), true),
                )
            )
        );
    }

    /**
     * @dataProvider invalidAddressesDataProvider
     * @param array $addresses
     * @param string $types
     */
    public function testValidateInvalid($addresses, $types)
    {
        $context = $this->getMockBuilder('Symfony\Component\Validator\ExecutionContext')
            ->disableOriginalConstructor()
            ->getMock();
        $context->expects($this->once())
            ->method('addViolation')
            ->with('Several addresses have the same type {{ types }}.', array('{{ types }}' => $types));

        $constraint = $this->getMock('Oro\Bundle\AddressBundle\Validator\Constraints\UniqueAddressTypes');
        $validator = new UniqueAddressTypesValidator();
        $validator->initialize($context);

        $validator->validate($addresses, $constraint);
    }

    /**
     * @return array
     */
    public function invalidAddressesDataProvider()
    {
        return array(
            'several addresses with one same type' => array(
                array(
                    $this->getTypedAddressMock(array('billing' => 'billing label')),
                    $this->getTypedAddressMock(array('billing' => 'billing label', 'shipping' => 'shipping label')),
                ),
                '"billing label"'
            ),
            'several addresses with two same types' => array(
                array(
                    $this->getTypedAddressMock(array('billing' => 'billing label')),
                    $this->getTypedAddressMock(array('shipping' => 'shipping label')),
                    $this->getTypedAddressMock(array('billing' => 'billing label', 'shipping' => 'shipping label')),
                ),
                '"billing label", "shipping label"'
            ),
        );
    }

    /**
     * Get address mock.
     *
     * @param array $addressTypes
     * @param bool $isEmpty
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getTypedAddressMock(array $addressTypes, $isEmpty = false)
    {
        $address = $this->getMockBuilder('Oro\Bundle\AddressBundle\Entity\AbstractTypedAddress')
            ->disableOriginalConstructor()
            ->setMethods(array('getTypes', 'isEmpty'))
            ->getMockForAbstractClass();

        $addressTypeEntities = array();
        foreach ($addressTypes as $name => $label) {
            $addressType = new AddressType($name);
            $addressType->setLabel($label);
            $addressTypeEntities[] = $addressType;
        }

        $address->expects($this->any())
            ->method('getTypes')
            ->will($this->returnValue($addressTypeEntities));

        $address->expects($this->once())
            ->method('isEmpty')
            ->will($this->returnValue($isEmpty));

        return $address;
    }
}
