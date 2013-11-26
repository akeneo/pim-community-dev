<?php

namespace Pim\Bundle\CatalogBundle\Tests\Unit\Validator\Constraints;

use Pim\Bundle\CatalogBundle\Validator\Constraints\File;
use Pim\Bundle\CatalogBundle\Validator\Constraints\FileValidator;

/**
 * Test related class
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FileValidatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @return array
     */
    public static function getValidData()
    {
        return array(
            array(''),
            array(null),
            array(__DIR__.'/../../../fixtures/akeneo.jpg'),
            array(new \SplFileInfo(__DIR__.'/../../../fixtures/akeneo.jpg')),
        );
    }

    /**
     * @return array
     */
    public static function getInvalidData()
    {
        return array(
            array(__DIR__.'/../../../fixtures/akeneo.jpg'),
            array(new \SplFileInfo(__DIR__.'/../../../fixtures/akeneo.jpg')),
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->target = new FileValidator();
    }

    /**
     * Test related method
     */
    public function testInstanceOfConstraintValidator()
    {
        $this->assertInstanceOf('Symfony\Component\Validator\Constraints\FileValidator', $this->target);
    }

    /**
     * @param mixed $file
     *
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
     * @param mixed $file
     *
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
     * @param mixed $file
     *
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
