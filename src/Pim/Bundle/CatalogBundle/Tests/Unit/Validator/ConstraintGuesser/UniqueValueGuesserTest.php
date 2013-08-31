<?php

namespace Pim\Bundle\CatalogBundle\Tests\Unit\Validator\ConstraintGuesser;

use Oro\Bundle\FlexibleEntityBundle\AttributeType\AbstractAttributeType;
use Pim\Bundle\CatalogBundle\Validator\ConstraintGuesser\UniqueValueGuesser;

/**
 * @author    Gildas Quemener <gildas.quemener@gmail.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class UniqueValueGuesserTest extends ConstraintGuesserTest
{
    public function setUp()
    {
        $this->target = new UniqueValueGuesser();
    }

    public function testInstanceOfContraintGuesserInterface()
    {
        $this->assertInstanceOf(
            'Oro\Bundle\FlexibleEntityBundle\Form\Validator\ConstraintGuesserInterface',
            $this->target
        );
    }

    public function testSupportVarcharAttribute()
    {
        $this->assertTrue(
            $this->target->supportAttribute(
                $this->getAttributeMock(array('backendType' => AbstractAttributeType::BACKEND_TYPE_VARCHAR))
            )
        );
    }

    public function testGuessUniqueValueConstraint()
    {
        $constraints = $this->target->guessConstraints(
            $this->getAttributeMock(
                array(
                    'backendType' => AbstractAttributeType::BACKEND_TYPE_VARCHAR,
                    'unique'      => true,
                )
            )
        );

        $this->assertContainsInstanceOf('Pim\Bundle\CatalogBundle\Validator\Constraints\UniqueValue', $constraints);
    }

    public function testDoNotGuessRangeConstraint()
    {
        $constraints = $this->target->guessConstraints(
            $this->getAttributeMock(
                array(
                    'backendType' => AbstractAttributeType::BACKEND_TYPE_VARCHAR,
                    'unique'      => false,
                )
            )
        );

        $this->assertEquals(0, count($constraints));
    }
}
