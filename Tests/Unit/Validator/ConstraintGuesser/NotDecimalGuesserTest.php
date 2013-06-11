<?php

namespace Pim\Bundle\ProductBundle\Tests\Unit\Validator\ConstraintGuesser;

use Pim\Bundle\ProductBundle\Validator\ConstraintGuesser\NotDecimalGuesser;
use Oro\Bundle\FlexibleEntityBundle\AttributeType\AbstractAttributeType;

/**
 * @author    Gildas Quemener <gildas.quemener@gmail.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class NotDecimalGuesserTest extends ConstraintGuesserTest
{
    public function setUp()
    {
        $this->target = new NotDecimalGuesser;
    }

    public function testInstanceOfContraintGuesserInterface()
    {
        $this->assertInstanceOf('Oro\Bundle\FlexibleEntityBundle\Form\Validator\ConstraintGuesserInterface', $this->target);
    }

    public function testSupportAttribute()
    {
        $this->assertTrue($this->target->supportAttribute(
            $this->getAttributeMock(array(
                'backendType' => AbstractAttributeType::BACKEND_TYPE_INTEGER
            ))
        ));

        $this->assertTrue($this->target->supportAttribute(
            $this->getAttributeMock(array(
                'backendType' => AbstractAttributeType::BACKEND_TYPE_METRIC
            ))
        ));

        $this->assertTrue($this->target->supportAttribute(
            $this->getAttributeMock(array(
                'backendType' => AbstractAttributeType::BACKEND_TYPE_PRICE
            ))
        ));
    }

    public function testGuessTypeConstraint()
    {
        $constraints = $this->target->guessConstraints($this->getAttributeMock(array(
            'backendType'     => AbstractAttributeType::BACKEND_TYPE_INTEGER,
            'decimalsAllowed' => false,
        )));

        $this->assertContainsInstanceOf('Pim\Bundle\ProductBundle\Validator\Constraints\NotDecimal', $constraints);
    }

    public function testDoNotGuessTypeConstraint()
    {
        $this->assertEquals(0, count($this->target->guessConstraints($this->getAttributeMock(array(
            'backendType'     => AbstractAttributeType::BACKEND_TYPE_INTEGER,
            'decimalsAllowed' => true,
        )))));
    }
}
