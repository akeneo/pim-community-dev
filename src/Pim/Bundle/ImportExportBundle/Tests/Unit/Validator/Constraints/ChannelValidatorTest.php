<?php

namespace Pim\Bundle\ImportExportBundle\Tests\Unit\Validator\Constraints;

use Pim\Bundle\ImportExportBundle\Validator\Constraints\Channel;
use Pim\Bundle\ImportExportBundle\Validator\Constraints\ChannelValidator;

/**
 * Test related class
 *
 * @author    Gildas Quemener <gildas.quemener@gmail.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ChannelValidatorTest extends \PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->context = $this->getMock('Symfony\Component\Validator\ExecutionContext', array(), array(), '', false);
        $this->validator = new ChannelValidator($this->getChannelManagerMock());
        $this->validator->initialize($this->context);
    }

    public function testInstanceOfConstraintValidator()
    {
        $this->assertInstanceOf('Symfony\Component\Validator\Constraints\ChoiceValidator', $this->validator);
    }

    public function testValidChannel()
    {
        $this->context->expects($this->never())
            ->method('addViolation');

        $this->validator->validate('foo', new Channel);
    }

    public function testInvalidChannel()
    {
        $constraint = new Channel();
        $this->context->expects($this->once())
            ->method('addViolation')
            ->with($constraint->message);

        $this->validator->validate('baz', $constraint);
    }

    private function getChannelManagerMock()
    {
        $manager = $this
            ->getMockBuilder('Pim\Bundle\ProductBundle\Manager\ChannelManager')
            ->disableOriginalConstructor()
            ->getMock();

        $manager->expects($this->any())
            ->method('getChannelChoices')
            ->will($this->returnValue(array('foo' => 'Foo', 'bar' => 'Bar',)));

        return $manager;
    }
}
