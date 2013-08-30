<?php

namespace Pim\Bundle\ProductBundle\Tests\Unit\Validator\ConstraintGuesser;

use Pim\Bundle\ProductBundle\Validator\ConstraintGuesser\RangeGuesser;

/**
 * @author    Gildas Quemener <gildas.quemener@gmail.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class RangeGuesserTest extends ConstraintGuesserTest
{
    public function setUp()
    {
        $this->target = new RangeGuesser();
    }

    public function testInstanceOfContraintGuesserInterface()
    {
        $this->assertInstanceOf(
            'Oro\Bundle\FlexibleEntityBundle\Form\Validator\ConstraintGuesserInterface',
            $this->target
        );
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

        $this->assertTrue(
            $this->target->supportAttribute($this->getAttributeMock(array('attributeType' => 'pim_product_date',)))
        );
    }

    public function testGuessMinConstraint()
    {
        $constraints = $this->target->guessConstraints(
            $this->getAttributeMock(
                array(
                    'attributeType' => 'pim_product_number',
                    'numberMin'     => 100,
                )
            )
        );

        $this->assertContainsInstanceOf('Pim\Bundle\ProductBundle\Validator\Constraints\Range', $constraints);
        $this->assertConstraintsConfiguration(
            'Pim\Bundle\ProductBundle\Validator\Constraints\Range',
            $constraints,
            array('min' => 100,)
        );
    }

    public function testGuessNegativeNotAllowedConstraint()
    {
        $constraints = $this->target->guessConstraints(
            $this->getAttributeMock(
                array(
                    'attributeType'   => 'pim_product_number',
                    'negativeAllowed' => false,
                    'numberMin'       => 100,
                )
            )
        );

        $this->assertContainsInstanceOf('Pim\Bundle\ProductBundle\Validator\Constraints\Range', $constraints);
        $this->assertConstraintsConfiguration(
            'Pim\Bundle\ProductBundle\Validator\Constraints\Range',
            $constraints,
            array('min' => 0,)
        );
    }

    public function testGuessMaxConstraint()
    {
        $constraints = $this->target->guessConstraints(
            $this->getAttributeMock(
                array(
                    'attributeType' => 'pim_product_number',
                    'numberMax'     => 300,
                )
            )
        );

        $this->assertContainsInstanceOf('Pim\Bundle\ProductBundle\Validator\Constraints\Range', $constraints);
        $this->assertConstraintsConfiguration(
            'Pim\Bundle\ProductBundle\Validator\Constraints\Range',
            $constraints,
            array('max' => 300)
        );
    }

    public function testGuessMinMaxConstraint()
    {
        $constraints = $this->target->guessConstraints(
            $this->getAttributeMock(
                array(
                    'attributeType' => 'pim_product_number',
                    'numberMin'     => 100,
                    'numberMax'     => 300,
                )
            )
        );

        $this->assertContainsInstanceOf('Pim\Bundle\ProductBundle\Validator\Constraints\Range', $constraints);
        $this->assertConstraintsConfiguration(
            'Pim\Bundle\ProductBundle\Validator\Constraints\Range',
            $constraints,
            array('min' => 100, 'max' => 300)
        );
    }

    public function testDoNotGuessRangeConstraint()
    {
        $constraints = $this->target->guessConstraints(
            $this->getAttributeMock(
                array('attributeType' => 'pim_product_number',)
            )
        );

        $this->assertEquals(0, count($constraints));
    }

    public function testGuessMinDateConstraint()
    {
        $constraints = $this->target->guessConstraints(
            $this->getAttributeMock(
                array('attributeType' => 'pim_product_date', 'dateMin' => '2012-01-01',)
            )
        );

        $this->assertContainsInstanceOf('Pim\Bundle\ProductBundle\Validator\Constraints\Range', $constraints);
        $this->assertConstraintsConfiguration(
            'Pim\Bundle\ProductBundle\Validator\Constraints\Range',
            $constraints,
            array('min' => '2012-01-01',)
        );
    }

    public function testGuessMaxDateConstraint()
    {
        $constraints = $this->target->guessConstraints(
            $this->getAttributeMock(
                array(
                    'attributeType' => 'pim_product_date',
                    'dateMax'       => '2013-05-14',
                )
            )
        );

        $this->assertContainsInstanceOf('Pim\Bundle\ProductBundle\Validator\Constraints\Range', $constraints);
        $this->assertConstraintsConfiguration(
            'Pim\Bundle\ProductBundle\Validator\Constraints\Range',
            $constraints,
            array(
                'max' => '2013-05-14',
            )
        );
    }

    public function testGuessMinMaxDateConstraint()
    {
        $constraints = $this->target->guessConstraints(
            $this->getAttributeMock(
                array(
                    'attributeType' => 'pim_product_date',
                    'dateMin'       => '2012-01-01',
                    'dateMax'       => '2013-05-14',
                )
            )
        );

        $this->assertContainsInstanceOf('Pim\Bundle\ProductBundle\Validator\Constraints\Range', $constraints);
        $this->assertConstraintsConfiguration(
            'Pim\Bundle\ProductBundle\Validator\Constraints\Range',
            $constraints,
            array('min' => '2012-01-01', 'max' => '2013-05-14',)
        );
    }
}
