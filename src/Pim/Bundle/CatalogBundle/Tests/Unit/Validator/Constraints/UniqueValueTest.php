<?php

namespace Pim\Bundle\CatalogBundle\Tests\Unit\Validator\Constraints;

use Pim\Bundle\CatalogBundle\Validator\Constraints\UniqueValue;

/**
 * Test related class
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class UniqueValueTest extends \PHPUnit_Framework_TestCase
{
    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->constraint = new UniqueValue();
    }

    /**
     * Test related method
     */
    public function testExtendsConstraint()
    {
        $this->assertInstanceOf('Symfony\Component\Validator\Constraint', $this->constraint);
    }

    /**
     * Test related method
     */
    public function testMessage()
    {
        $this->assertEquals(
            'This value is already set on another product.',
            $this->constraint->message
        );
    }

    /**
     * Test related method
     */
    public function testValidatedBy()
    {
        $this->assertEquals('pim_unique_value_validator', $this->constraint->validatedBy());
    }
}
