<?php

namespace Pim\Bundle\ProductBundle\Tests\Unit\Validator\ConstraintGuesser;

use Oro\Bundle\FlexibleEntityBundle\AttributeType\AbstractAttributeType;
use Pim\Bundle\ProductBundle\Validator\ConstraintGuesser\LengthGuesser;

/**
 * @author    Gildas Quemener <gildas.quemener@gmail.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class LengthGuesserTest extends ConstraintGuesserTest
{
    public function setUp()
    {
        $this->target = new LengthGuesser;
    }

    public function testInstanceOfContraintGuesserInterface()
    {
        $this->assertInstanceOf('Oro\Bundle\FlexibleEntityBundle\Form\Validator\ConstraintGuesserInterface', $this->target);
    }

    public function testSupportTextAndVarcharAttribute()
    {
        $this->assertTrue($this->target->supportAttribute(
            $this->getAttributeMock(array(
                'backendType' => AbstractAttributeType::BACKEND_TYPE_TEXT
            ))
        ));

        $this->assertTrue($this->target->supportAttribute(
            $this->getAttributeMock(array(
                'backendType' => AbstractAttributeType::BACKEND_TYPE_VARCHAR
            ))
        ));
    }

    public function testGuessMinMaxConstraint()
    {
        $constraints = $this->target->guessConstraints($this->getAttributeMock(array(
            'backendType'   => AbstractAttributeType::BACKEND_TYPE_TEXT,
            'maxCharacters' => 128,
        )));

        $this->assertContainsInstanceOf('Symfony\Component\Validator\Constraints\Length', $constraints);
        $this->assertConstraintsConfiguration('Symfony\Component\Validator\Constraints\Length', $constraints, array(
            'max' => 128
        ));
    }

    public function testDoNotGuessRangeConstraint()
    {
        $constraints = $this->target->guessConstraints($this->getAttributeMock(array(
            'backendType' => AbstractAttributeType::BACKEND_TYPE_VARCHAR,
        )));

        $this->assertEquals(0, count($constraints));
    }
}
