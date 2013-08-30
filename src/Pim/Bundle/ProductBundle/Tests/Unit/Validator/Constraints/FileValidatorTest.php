<?php

namespace Pim\Bundle\ProductBundle\Tests\Unit\Validator\Constraints;

use Pim\Bundle\ProductBundle\Validator\Constraints\File;
use Pim\Bundle\ProductBundle\Validator\Constraints\FileValidator;

/**
 * @author    Gildas Quemener <gildas.quemener@gmail.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FileValidatorTest extends \PHPUnit_Framework_TestCase
{
    public static function getValidData()
    {
        return array(
            array(''),
            array(null),
            array(__DIR__.'/../../../fixtures/akeneo.jpg'),
            array(new \SplFileInfo(__DIR__.'/../../../fixtures/akeneo.jpg')),
        );
    }

    public static function getInvalidData()
    {
        return array(
            array(__DIR__.'/../../../fixtures/akeneo.jpg'),
            array(new \SplFileInfo(__DIR__.'/../../../fixtures/akeneo.jpg')),
        );
    }

    public function setUp()
    {
        $this->target = new FileValidator();
    }

    public function testInstanceOfConstraintValidator()
    {
        $this->assertInstanceOf('Symfony\Component\Validator\Constraints\FileValidator', $this->target);
    }

    /**
     * @dataProvider getValidData
     */
    public function testValidValue($file)
    {
        $constraint = new File(
            array('allowedExtensions' => array('gif', 'jpg'))
        );

        $context = $this
            ->getMockBuilder('Symfony\Component\Validator\ExecutionContext')
            ->disableOriginalConstructor()
            ->getMock();

        $context->expects($this->never())
            ->method('addViolation');

        $this->target->initialize($context);
        $this->target->validate($file, $constraint);
    }

    /**
     * @dataProvider getInvalidData
     */
    public function testInvalidValue($file)
    {
        $constraint = new File(
            array('allowedExtensions' => array('gif', 'png'))
        );

        $context = $this
            ->getMockBuilder('Symfony\Component\Validator\ExecutionContext')
            ->disableOriginalConstructor()
            ->getMock();

        $context->expects($this->once())
            ->method('addViolation')
            ->with(
                $constraint->extensionsMessage,
                array('{{ extensions }}' => 'gif, png')
            );

        $this->target->initialize($context);
        $this->target->validate($file, $constraint);
    }

    /**
     * @dataProvider getValidData
     */
    public function testEmptyAllowedExtensions($file)
    {
        $constraint = new File();

        $context = $this
            ->getMockBuilder('Symfony\Component\Validator\ExecutionContext')
            ->disableOriginalConstructor()
            ->getMock();

        $context->expects($this->never())
            ->method('addViolation');

        $this->target->initialize($context);
        $this->target->validate($file, $constraint);
    }
}
