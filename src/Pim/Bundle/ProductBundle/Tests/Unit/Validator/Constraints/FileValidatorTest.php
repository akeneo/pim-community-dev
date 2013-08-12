<?php

namespace Pim\Bundle\ProductBundle\Tests\Unit\Validator\Constraints;

use Symfony\Component\HttpFoundation\File\File as FileObject;
use Pim\Bundle\ProductBundle\Validator\Constraints\File;
use Pim\Bundle\ProductBundle\Validator\Constraints\FileValidator;

/**
 * @author    Gildas Quemener <gildas.quemener@gmail.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FileValidatorTest extends \PHPUnit_Framework_TestCase
{
    public static function getValidateData()
    {
        return array(
            array(__DIR__.'/../../../fixtures/akeneo.jpg'),
            array(new FileObject(__DIR__.'/../../../fixtures/akeneo.jpg')),
        );
    }

    public function setUp()
    {
        $this->target = new FileValidator;
    }

    public function testInstanceOfConstraintValidator()
    {
        $this->assertInstanceOf('Symfony\Component\Validator\Constraints\FileValidator', $this->target);
    }

    /**
     * @dataProvider getValidateData
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
     * @dataProvider getValidateData
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
                array('{{ extensions }}' => array('gif', 'png'))
            );

        $this->target->initialize($context);
        $this->target->validate($file, $constraint);
    }

    /**
     * @dataProvider getValidateData
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
