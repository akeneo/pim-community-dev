<?php

namespace Pim\Bundle\ImportExportBundle\Tests\Unit\Twig;

use Pim\Bundle\ImportExportBundle\Twig\NormalizeConfigurationExtension;

/**
 * Test related class
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class NormalizeConfigurationExtensionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->extension = new NormalizeConfigurationExtension();
    }

    /**
     * Test related method
     */
    public function testInstanceOfTwigExtension()
    {
        $this->assertInstanceOf('\Twig_Extension', $this->extension);
    }

    /**
     * Test related method
     */
    public function testGetName()
    {
        $this->assertEquals('pim_ie_normalize_configuration', $this->extension->getName());
    }

    /**
     * Data provider for testNormalizeValues
     *
     * @return array
     */
    public static function getNormalizeValuesData()
    {
        return [
            [true, 'Yes'],
            [false, 'No'],
            ['foo', 'foo'],
            [0, 0],
            [1, 1],
            [2, 2],
            [null, 'N/A']
        ];
    }

    /**
     * @param mixed $value
     * @param mixed $expectedValue
     *
     * @dataProvider getNormalizeValuesData
     */
    public function testNormalizeValues($value, $expectedValue)
    {
        $this->assertEquals($expectedValue, $this->extension->normalizeValueFilter($value));
    }

    /**
     * Test related method
     */
    public function testGetViolationsFunction()
    {
        $violations = [
            $this->getViolationMock('job.steps[0].reader.foo', 'The reader foo of step 0 is somehow wrong.'),
            $this->getViolationMock('job.steps[1].writer.bar', 'The writer bar of step 1 is somehow wrong.'),
            $this->getViolationMock('job.steps[1].writer.bar', 'The writer bar of step 1 is elsehow wrong.'),
        ];

        $this->assertEquals(
            '<span class="label label-important">The reader foo of step 0 is somehow wrong.</span>',
            $this->extension->getViolationsFunction($violations, 'foo')
        );
        $this->assertEquals(
            '<span class="label label-important">The writer bar of step 1 is somehow wrong.</span>&nbsp;' .
            '<span class="label label-important">The writer bar of step 1 is elsehow wrong.</span>',
            $this->extension->getViolationsFunction($violations, 'bar')
        );
        $this->assertEmpty(
            $this->extension->getViolationsFunction($violations, 'baz')
        );
    }

    /**
     * Get a ConstraintViolation mock
     *
     * @param string $propertyPath
     * @param string $message
     *
     * @return \Symfony\Component\Validator\ConstraintViolation
     */
    protected function getViolationMock($propertyPath, $message)
    {
        $constraint = $this
            ->getMockBuilder('Symfony\Component\Validator\ConstraintViolation')
            ->disableOriginalConstructor()
            ->getMock();

        $constraint->expects($this->any())
            ->method('getPropertyPath')
            ->will($this->returnValue($propertyPath));

        $constraint->expects($this->any())
            ->method('getMessage')
            ->will($this->returnValue($message));

        return $constraint;
    }
}
