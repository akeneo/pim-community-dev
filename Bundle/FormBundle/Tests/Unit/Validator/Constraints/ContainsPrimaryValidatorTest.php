<?php

namespace Oro\Bundle\FormBundle\Tests\Unit\Validator\Constraints;

use Oro\Bundle\FormBundle\Validator\Constraints\ContainsPrimaryValidator;

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
     * @dataProvider validItemsDataProvider
     * @param array $items
     */
    public function testValidateValid(array $items)
    {
        $context = $this->getMockBuilder('Symfony\Component\Validator\ExecutionContext')
            ->disableOriginalConstructor()
            ->getMock();
        $context->expects($this->never())
            ->method('addViolation');

        $constraint = $this->getMock('Oro\Bundle\FormBundle\Validator\Constraints\ContainsPrimary');
        $validator = new ContainsPrimaryValidator();
        $validator->initialize($context);

        $validator->validate($items, $constraint);
    }

    /**
     * @return array
     */
    public function validItemsDataProvider()
    {
        return array(
            'no items' => array(
                array()
            ),
            'one item primary' => array(
                array($this->getPrimaryItemMock(true))
            ),
            'more than one item with primary' => array(
                array($this->getPrimaryItemMock(false), $this->getPrimaryItemMock(true))
            ),
            'empty item and primary' => array(
                array(
                    $this->getPrimaryItemMock(false, true),
                    $this->getPrimaryItemMock(true),
                    $this->getPrimaryItemMock(false, true)
                )
            )
        );
    }

    /**
     * @dataProvider invalidItemsDataProvider
     * @param array $items
     */
    public function testValidateInvalid($items)
    {
        $context = $this->getMockBuilder('Symfony\Component\Validator\ExecutionContext')
            ->disableOriginalConstructor()
            ->getMock();
        $context->expects($this->once())
            ->method('addViolation')
            ->with('One of items must be set as primary.');

        $constraint = $this->getMock('Oro\Bundle\FormBundle\Validator\Constraints\ContainsPrimary');
        $validator = new ContainsPrimaryValidator();
        $validator->initialize($context);

        $validator->validate($items, $constraint);
    }

    /**
     * @return array
     */
    public function invalidItemsDataProvider()
    {
        return array(
            'one item' => array(
                array($this->getPrimaryItemMock(false))
            ),
            'more than one item no primary' => array(
                array($this->getPrimaryItemMock(false), $this->getPrimaryItemMock(false))
            ),
            'more than one item more than one primary' => array(
                array($this->getPrimaryItemMock(true), $this->getPrimaryItemMock(true))
            ),
        );
    }

    /**
     * Get primary item mock.
     *
     * @param bool $isPrimary
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getPrimaryItemMock($isPrimary)
    {
        $item = $this->getMockBuilder('Oro\Bundle\FormBundle\Entity\PrimaryItem')
            ->disableOriginalConstructor()
            ->getMock();

        $item->expects($this->any())
            ->method('isPrimary')
            ->will($this->returnValue($isPrimary));

        return $item;
    }
}
