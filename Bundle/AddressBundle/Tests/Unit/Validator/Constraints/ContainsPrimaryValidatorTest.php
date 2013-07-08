<?php

namespace Oro\Bundle\AddressBundle\Tests\Unit\Validator\Constraints;

use Oro\Bundle\AddressBundle\Validator\Constraints\ContainsPrimaryValidator;

class ContainsPrimaryValidatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException \Symfony\Component\Validator\Exception\UnexpectedTypeException
     * @expectedExceptionMessage Expected argument of type array or Traversable and ArrayAccess, boolean given
     */
    public function testValidateException()
    {
        $constraint = $this->getMock('Symfony\Component\Validator\Constraint');
        $validator = new ContainsPrimaryValidator();
        $validator->validate(false, $constraint);
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

        $constraint = $this->getMock('Oro\Bundle\AddressBundle\Validator\Constraints\ContainsPrimary');
        $validator = new ContainsPrimaryValidator();
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
            'one address primary' => array(
                array($this->getTypedAddressMock(true))
            ),
            'more than one address with primary' => array(
                array($this->getTypedAddressMock(false), $this->getTypedAddressMock(true))
            ),
            'empty address' => array(
                array($this->getTypedAddressMock(false, true), $this->getTypedAddressMock(false, true))
            ),
            'empty address and primary' => array(
                array($this->getTypedAddressMock(false, true), $this->getTypedAddressMock(true), $this->getTypedAddressMock(false, true))
            )
        );
    }

    /**
     * @dataProvider invalidAddressesDataProvider
     * @param array $addresses
     */
    public function testValidateInvalid($addresses)
    {
        $context = $this->getMockBuilder('Symfony\Component\Validator\ExecutionContext')
            ->disableOriginalConstructor()
            ->getMock();
        $context->expects($this->once())
            ->method('addViolation')
            ->with('One of addresses must be set as primary.');

        $constraint = $this->getMock('Oro\Bundle\AddressBundle\Validator\Constraints\ContainsPrimary');
        $validator = new ContainsPrimaryValidator();
        $validator->initialize($context);

        $validator->validate($addresses, $constraint);
    }

    /**
     * @return array
     */
    public function invalidAddressesDataProvider()
    {
        return array(
            'one address' => array(
                array($this->getTypedAddressMock(false))
            ),
            'more than one address no primary' => array(
                array($this->getTypedAddressMock(false), $this->getTypedAddressMock(false))
            ),
            'more than one address more than one primary' => array(
                array($this->getTypedAddressMock(true), $this->getTypedAddressMock(true))
            ),
        );
    }

    /**
     * Get address mock.
     *
     * @param bool $isPrimary
     * @param bool $isEmpty
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getTypedAddressMock($isPrimary, $isEmpty = false)
    {
        $address = $this->getMockBuilder('Oro\Bundle\AddressBundle\Entity\AbstractTypedAddress')
            ->disableOriginalConstructor()
            ->getMock();
        $address->expects($this->any())
            ->method('isPrimary')
            ->will($this->returnValue($isPrimary));
        $address->expects($this->once())
            ->method('isEmpty')
            ->will($this->returnValue($isEmpty));
        return $address;
    }
}
