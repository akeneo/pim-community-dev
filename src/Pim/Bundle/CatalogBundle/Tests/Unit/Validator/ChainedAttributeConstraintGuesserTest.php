<?php

namespace Pim\Bundle\CatalogBundle\Tests\Unit\Validator;

use Pim\Bundle\CatalogBundle\Tests\Unit\Validator\ConstraintGuesser\ConstraintGuesserTest;
use Pim\Bundle\CatalogBundle\Validator\ChainedAttributeConstraintGuesser;

/**
 * Test related class
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ChainedAttributeConstraintGuesserTest extends ConstraintGuesserTest
{
    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->target = new ChainedAttributeConstraintGuesser();
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
    public function testAddConstraintGuesser()
    {
        $this->target->addConstraintGuesser(
            $this->getMock('Pim\Bundle\FlexibleEntityBundle\Form\Validator\ConstraintGuesserInterface')
        );
        $this->assertCount(1, $this->target->getConstraintGuessers());
    }

    /**
     * Test related method
     */
    public function testSupportAttribute()
    {
        $this->assertTrue($this->target->supportAttribute($this->getAttributeMock()));
    }

    /**
     * Test related method
     */
    public function testGuessConstraintsUsingRegisteredGuessers()
    {
        $guesser1 = $this->getMock('Pim\Bundle\FlexibleEntityBundle\Form\Validator\ConstraintGuesserInterface');
        $guesser2 = $this->getMock('Pim\Bundle\FlexibleEntityBundle\Form\Validator\ConstraintGuesserInterface');
        $guesser3 = $this->getMock('Pim\Bundle\FlexibleEntityBundle\Form\Validator\ConstraintGuesserInterface');

        $guesser1->expects($this->any())->method('supportAttribute')->will($this->returnValue(true));
        $guesser2->expects($this->any())->method('supportAttribute')->will($this->returnValue(false));
        $guesser3->expects($this->any())->method('supportAttribute')->will($this->returnValue(true));

        $guesser1->expects($this->once())->method('guessConstraints')->will($this->returnValue(['foo']));
        $guesser2->expects($this->never())->method('guessConstraints');
        $guesser3->expects($this->once())->method('guessConstraints')->will($this->returnValue(['bar']));

        $this->target->addConstraintGuesser($guesser1);
        $this->target->addConstraintGuesser($guesser2);
        $this->target->addConstraintGuesser($guesser3);

        $this->assertEquals(['foo', 'bar'], $this->target->guessConstraints($this->getAttributeMock()));
    }
}
