<?php

namespace Pim\Bundle\CatalogBundle\Tests\Unit\Validator\ConstraintGuesser;

use Pim\Bundle\CatalogBundle\Validator\ConstraintGuesser\RangeGuesser;

/**
 * Test related class
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class RangeGuesserTest extends ConstraintGuesserTest
{
    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->target = new RangeGuesser();
    }

    /**
     * Test related method
     */
    public function testInstanceOfContraintGuesserInterface()
    {
        $this->assertInstanceOf(
            'Oro\Bundle\FlexibleEntityBundle\Form\Validator\ConstraintGuesserInterface',
            $this->target
        );
    }

    /**
     * Test related method
     */
    public function testSupportAttribute()
    {
        $this->assertTrue(
            $this->target->supportAttribute($this->getAttributeMock(array('attributeType' => 'pim_catalog_metric')))
        );

        $this->assertTrue(
            $this->target->supportAttribute($this->getAttributeMock(array('attributeType' => 'pim_catalog_number')))
        );

        $this->assertTrue(
            $this->target->supportAttribute($this->getAttributeMock(array('attributeType' => 'pim_catalog_date')))
        );
    }

    /**
     * Test related method
     */
    public function testGuessMinConstraint()
    {
        $constraints = $this->target->guessConstraints(
            $this->getAttributeMock(
                array(
                    'attributeType' => 'pim_catalog_number',
                    'numberMin'     => 100,
                )
            )
        );

        $this->assertContainsInstanceOf('Pim\Bundle\CatalogBundle\Validator\Constraints\Range', $constraints);
        $this->assertConstraintsConfiguration(
            'Pim\Bundle\CatalogBundle\Validator\Constraints\Range',
            $constraints,
            array('min' => 100)
        );
    }

    /**
     * Test related method
     */
    public function testGuessNegativeNotAllowedConstraint()
    {
        $constraints = $this->target->guessConstraints(
            $this->getAttributeMock(
                array(
                    'attributeType'   => 'pim_catalog_number',
                    'negativeAllowed' => false,
                    'numberMin'       => 100,
                )
            )
        );

        $this->assertContainsInstanceOf('Pim\Bundle\CatalogBundle\Validator\Constraints\Range', $constraints);
        $this->assertConstraintsConfiguration(
            'Pim\Bundle\CatalogBundle\Validator\Constraints\Range',
            $constraints,
            array('min' => 0)
        );
    }

    /**
     * Test related method
     */
    public function testGuessMaxConstraint()
    {
        $constraints = $this->target->guessConstraints(
            $this->getAttributeMock(
                array(
                    'attributeType' => 'pim_catalog_number',
                    'numberMax'     => 300,
                )
            )
        );

        $this->assertContainsInstanceOf('Pim\Bundle\CatalogBundle\Validator\Constraints\Range', $constraints);
        $this->assertConstraintsConfiguration(
            'Pim\Bundle\CatalogBundle\Validator\Constraints\Range',
            $constraints,
            array('max' => 300)
        );
    }

    /**
     * Test related method
     */
    public function testGuessMinMaxConstraint()
    {
        $constraints = $this->target->guessConstraints(
            $this->getAttributeMock(
                array(
                    'attributeType' => 'pim_catalog_number',
                    'numberMin'     => 100,
                    'numberMax'     => 300,
                )
            )
        );

        $this->assertContainsInstanceOf('Pim\Bundle\CatalogBundle\Validator\Constraints\Range', $constraints);
        $this->assertConstraintsConfiguration(
            'Pim\Bundle\CatalogBundle\Validator\Constraints\Range',
            $constraints,
            array('min' => 100, 'max' => 300)
        );
    }

    /**
     * Test related method
     */
    public function testDoNotGuessRangeConstraint()
    {
        $constraints = $this->target->guessConstraints(
            $this->getAttributeMock(
                array('attributeType' => 'pim_catalog_number')
            )
        );

        $this->assertEquals(0, count($constraints));
    }

    /**
     * Test related method
     */
    public function testGuessMinDateConstraint()
    {
        $constraints = $this->target->guessConstraints(
            $this->getAttributeMock(
                array('attributeType' => 'pim_catalog_date', 'dateMin' => '2012-01-01')
            )
        );

        $this->assertContainsInstanceOf('Pim\Bundle\CatalogBundle\Validator\Constraints\Range', $constraints);
        $this->assertConstraintsConfiguration(
            'Pim\Bundle\CatalogBundle\Validator\Constraints\Range',
            $constraints,
            array('min' => '2012-01-01')
        );
    }

    /**
     * Test related method
     */
    public function testGuessMaxDateConstraint()
    {
        $constraints = $this->target->guessConstraints(
            $this->getAttributeMock(
                array(
                    'attributeType' => 'pim_catalog_date',
                    'dateMax'       => '2013-05-14',
                )
            )
        );

        $this->assertContainsInstanceOf('Pim\Bundle\CatalogBundle\Validator\Constraints\Range', $constraints);
        $this->assertConstraintsConfiguration(
            'Pim\Bundle\CatalogBundle\Validator\Constraints\Range',
            $constraints,
            array(
                'max' => '2013-05-14',
            )
        );
    }

    /**
     * Test related method
     */
    public function testGuessMinMaxDateConstraint()
    {
        $constraints = $this->target->guessConstraints(
            $this->getAttributeMock(
                array(
                    'attributeType' => 'pim_catalog_date',
                    'dateMin'       => '2012-01-01',
                    'dateMax'       => '2013-05-14',
                )
            )
        );

        $this->assertContainsInstanceOf('Pim\Bundle\CatalogBundle\Validator\Constraints\Range', $constraints);
        $this->assertConstraintsConfiguration(
            'Pim\Bundle\CatalogBundle\Validator\Constraints\Range',
            $constraints,
            array('min' => '2012-01-01', 'max' => '2013-05-14')
        );
    }
}
