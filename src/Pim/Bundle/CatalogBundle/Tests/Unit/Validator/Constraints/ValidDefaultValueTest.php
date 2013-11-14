<?php

namespace Pim\Bundle\CatalogBundle\Tests\Unit\Validator\Constraints;

use Pim\Bundle\CatalogBundle\Validator\Constraints\ValidDefaultValue;

/**
 * Valid default value constraint test
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ValidDefaultValueTest extends \PHPUnit_Framework_TestCase
{
    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->target = new ValidDefaultValue();
    }

    /**
     * Test violation messages
     */
    public function testMinDateMessage()
    {
        $this->assertEquals('This date format is not valid.', $this->target->dateFormatMessage);
        $this->assertEquals('This value should be between the min and max date.', $this->target->dateMessage);
        $this->assertEquals('This value should be greater than or equal to 0', $this->target->negativeMessage);
        $this->assertEquals('This value should be between the min and max number.', $this->target->numberMessage);
        $this->assertEquals('This value should be a whole number.', $this->target->decimalsMessage);
        $this->assertEquals('This value should not exceed max characters.', $this->target->charactersMessage);
        $this->assertEquals('This value should match the regular expression.', $this->target->regexpMessage);
    }

    /**
     * Test property path
     */
    public function testPropertyPath()
    {
        $this->assertEquals('defaultValue', $this->target->propertyPath);
    }

    /**
     * Test targets
     */
    public function testTargets()
    {
        $this->assertEquals(\Symfony\Component\Validator\Constraint::CLASS_CONSTRAINT, $this->target->getTargets());
    }
}
