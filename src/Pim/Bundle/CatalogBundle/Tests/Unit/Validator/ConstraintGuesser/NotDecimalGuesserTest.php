<?php

namespace Pim\Bundle\CatalogBundle\Tests\Unit\Validator\ConstraintGuesser;

use Pim\Bundle\CatalogBundle\Validator\ConstraintGuesser\NotDecimalGuesser;

/**
 * Test related class
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class NotDecimalGuesserTest extends ConstraintGuesserTest
{
    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->target = new NotDecimalGuesser();
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
    }

    /**
     * Test related method
     */
    public function testGuessNotDecimalConstraint()
    {
        $constraints = $this->target->guessConstraints(
            $this->getAttributeMock(array('attributeType' => 'pim_catalog_number', 'decimalsAllowed' => false))
        );

        $this->assertContainsInstanceOf('Pim\Bundle\CatalogBundle\Validator\Constraints\NotDecimal', $constraints);
    }

    /**
     * Test related method
     */
    public function testDoNotGuessNotDecimalConstraint()
    {
        $this->assertEquals(
            0,
            count(
                $this->target->guessConstraints(
                    $this->getAttributeMock(array('attributeType' => 'pim_catalog_number', 'decimalsAllowed' => true))
                )
            )
        );
    }
}
