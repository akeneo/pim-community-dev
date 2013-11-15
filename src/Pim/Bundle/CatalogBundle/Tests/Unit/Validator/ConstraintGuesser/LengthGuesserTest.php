<?php

namespace Pim\Bundle\CatalogBundle\Tests\Unit\Validator\ConstraintGuesser;

use Pim\Bundle\CatalogBundle\Validator\ConstraintGuesser\LengthGuesser;

/**
 * Test related class
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class LengthGuesserTest extends ConstraintGuesserTest
{
    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->target = new LengthGuesser();
    }

    /**
     * Test related method
     */
    public function testInstanceOfContraintGuesserInterface()
    {
        $this->assertInstanceOf(
            'Pim\Bundle\FlexibleEntityBundle\Form\Validator\ConstraintGuesserInterface',
            $this->target
        );
    }

    /**
     * Test related method
     */
    public function testSupportAttribute()
    {
        $this->assertTrue(
            $this->target->supportAttribute($this->getAttributeMock(array('attributeType' => 'pim_catalog_text')))
        );

        $this->assertTrue(
            $this->target->supportAttribute($this->getAttributeMock(array('attributeType' => 'pim_catalog_textarea')))
        );

        $this->assertTrue(
            $this->target->supportAttribute(
                $this->getAttributeMock(array('attributeType' => 'pim_catalog_identifier'))
            )
        );
    }

    /**
     * Test related method
     */
    public function testGuessLengthConstraint()
    {
        $constraints = $this->target->guessConstraints(
            $this->getAttributeMock(array('attributeType' => 'pim_catalog_text', 'maxCharacters' => 128))
        );

        $this->assertContainsInstanceOf('Symfony\Component\Validator\Constraints\Length', $constraints);
        $this->assertConstraintsConfiguration(
            'Symfony\Component\Validator\Constraints\Length',
            $constraints,
            array('max' => 128)
        );
    }

    /**
     * Test related method
     */
    public function testDoNotGuessLengthConstraint()
    {
        $constraints = $this->target->guessConstraints(
            $this->getAttributeMock(array('attributeType' => 'pim_catalog_text'))
        );

        $this->assertEquals(0, count($constraints));
    }
}
