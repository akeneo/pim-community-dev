<?php

namespace Pim\Bundle\ProductBundle\Tests\Unit\Validator\ConstraintGuesser;

use Pim\Bundle\ProductBundle\Validator\ConstraintGuesser\MinGuesser;
use Oro\Bundle\FlexibleEntityBundle\AttributeType\AbstractAttributeType;

/**
 * @author    Gildas Quemener <gildas.quemener@gmail.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MinGuesserTest extends ConstraintGuesserTest
{
    public function setUp()
    {
        $this->target = new MinGuesser;
    }

    public function testInstanceOfContraintGuesserInterface()
    {
        $this->assertInstanceOf('Oro\Bundle\FlexibleEntityBundle\Form\Validator\ConstraintGuesserInterface', $this->target);
    }

    public function testSupportAnyAttribute()
    {
        $this->assertTrue($this->target->supportAttribute(
            $this->getAttributeMock(array(
                'backendType' => AbstractAttributeType::BACKEND_TYPE_INTEGER
            ))
        ));
    }

    public function testGuessMinConstraint()
    {
        $constraints = $this->target->guessConstraints($this->getAttributeMock(array(
            'backendType'     => AbstractAttributeType::BACKEND_TYPE_INTEGER,
            'negativeAllowed' => false,
        )));

        $this->assertContainsInstanceOf('Symfony\Component\Validator\Constraints\Min', $constraints);
        $this->assertConstraintsConfiguration('Symfony\Component\Validator\Constraints\Min', $constraints, array(
            'limit' => 0,
        ));
    }

    public function testDoNotGuessMinConstraint()
    {
        $this->assertEquals(0, count($this->target->guessConstraints($this->getAttributeMock(array(
            'backendType'     => AbstractAttributeType::BACKEND_TYPE_INTEGER,
            'negativeAllowed' => true,
        )))));
    }
}
