<?php

namespace Pim\Bundle\ImportExportBundle\Tests\Unit\Twig;

use Pim\Bundle\ImportExportBundle\Twig\NormalizeConfigurationExtension;

/**
 * Test related class
 *
 * @author    Gildas Quemener <gildas.quemener@gmail.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class NormalizeConfigurationExtensionTest extends \PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->extension = new NormalizeConfigurationExtension;
    }

    public function testInstanceOfTwigExtension()
    {
        $this->assertInstanceOf('\Twig_Extension', $this->extension);
    }

    public function testGetName()
    {
        $this->assertEquals('pim_ie_normalize_configuration', $this->extension->getName());
    }

    public static function getNormalizeValuesData()
    {
        return array(
            array(true, 'Yes'),
            array(false, 'No'),
            array('foo', 'foo'),
            array(1, 1),
            array(null, 'N/A')
        );
    }

    /**
     * @dataProvider getNormalizeValuesData
     */
    public function testNormalizeValues($value, $expectedValue)
    {
        $this->assertEquals($expectedValue, $this->extension->normalizeValueFilter($value));
    }

    public function testGetViolationsFunction()
    {
        $violations = array(
            $this->getViolationMock('jobDefinition.steps[0].reader.foo', 'The reader foo of step 0 is somehow wrong.'),
            $this->getViolationMock('jobDefinition.steps[1].writer.bar', 'The writer bar of step 1 is somehow wrong.'),
            $this->getViolationMock('jobDefinition.steps[1].writer.bar', 'The writer bar of step 1 is elsehow wrong.'),
        );

        $this->assertEquals(
            'The reader foo of step 0 is somehow wrong.',
            $this->extension->getViolationsFunction($violations, 0, 'Reader', 'foo')
        );
        $this->assertEquals(
            'The writer bar of step 1 is somehow wrong. The writer bar of step 1 is elsehow wrong.',
            $this->extension->getViolationsFunction($violations, 1, 'Writer', 'bar')
        );
        $this->assertEmpty(
            $this->extension->getViolationsFunction($violations, 2, 'Writer', 'bar')
        );
    }

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
