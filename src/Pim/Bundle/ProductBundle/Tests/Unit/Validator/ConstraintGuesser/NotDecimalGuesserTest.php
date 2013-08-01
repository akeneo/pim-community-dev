<?php

namespace Pim\Bundle\ProductBundle\Tests\Unit\Validator\ConstraintGuesser;

use Pim\Bundle\ProductBundle\Validator\ConstraintGuesser\NotDecimalGuesser;

/**
 * Test related class
 *
 * @author    Gildas Quemener <gildas.quemener@gmail.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class NotDecimalGuesserTest extends ConstraintGuesserTest
{

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        $this->target = new NotDecimalGuesser();
    }

    public function testInstanceOfContraintGuesserInterface()
    {
        $this->assertInstanceOf('Oro\Bundle\FlexibleEntityBundle\Form\Validator\ConstraintGuesserInterface', $this->target);
    }

    public function testSupportAttribute()
    {
        $this->assertTrue(
            $this->target->supportAttribute(
                $this->getAttributeMock(array('attributeType' => 'pim_product_price_collection',))
            )
        );

        $this->assertTrue(
            $this->target->supportAttribute($this->getAttributeMock(array('attributeType' => 'pim_product_metric',)))
        );

        $this->assertTrue(
            $this->target->supportAttribute($this->getAttributeMock(array('attributeType' => 'pim_product_number',)))
        );
    }

    public function testGuessNotDecimalConstraint()
    {
        $constraints = $this->target->guessConstraints(
            $this->getAttributeMock(array('attributeType'   => 'pim_product_number', 'decimalsAllowed' => false,))
        );

        $this->assertContainsInstanceOf('Pim\Bundle\ProductBundle\Validator\Constraints\NotDecimal', $constraints);
    }

    public function testDoNotGuessNotDecimalConstraint()
    {
        $this->assertEquals(
            0,
            count($this->target->guessConstraints($this->getAttributeMock(array('attributeType'   => 'pim_product_number','decimalsAllowed' => true,))))
        );
    }
}
