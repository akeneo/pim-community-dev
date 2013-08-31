<?php

namespace Pim\Bundle\CatalogBundle\Tests\Unit\Validator;

use Pim\Bundle\CatalogBundle\Tests\Unit\Validator\ConstraintGuesser\ConstraintGuesserTest;
use Pim\Bundle\CatalogBundle\Validator\ChainedAttributeConstraintGuesser;

/**
 * @author    Gildas Quemener <gildas.quemener@gmail.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ChainedAttributeConstraintGuesserTest extends ConstraintGuesserTest
{
    public function setUp()
    {
        $this->target = new ChainedAttributeConstraintGuesser();
    }

    public function testInstanceOfContraintGuesserInterface()
    {
        $this->assertInstanceOf(
            'Oro\Bundle\FlexibleEntityBundle\Form\Validator\ConstraintGuesserInterface',
            $this->target
        );
    }

    public function testAddConstraintGuesser()
    {
        $this->target->addConstraintGuesser(
            $this->getMock('Oro\Bundle\FlexibleEntityBundle\Form\Validator\ConstraintGuesserInterface')
        );
        $this->assertCount(1, $this->target->getConstraintGuessers());
    }

    public function testSupportProductAttribute()
    {
        $this->assertTrue($this->target->supportAttribute($this->getAttributeMock()));
    }

    public function testGuessConstraintsUsingRegisteredGuessers()
    {
        $guesser1 = $this->getMock('Oro\Bundle\FlexibleEntityBundle\Form\Validator\ConstraintGuesserInterface');
        $guesser2 = $this->getMock('Oro\Bundle\FlexibleEntityBundle\Form\Validator\ConstraintGuesserInterface');
        $guesser3 = $this->getMock('Oro\Bundle\FlexibleEntityBundle\Form\Validator\ConstraintGuesserInterface');

        $guesser1->expects($this->any())->method('supportAttribute')->will($this->returnValue(true));
        $guesser2->expects($this->any())->method('supportAttribute')->will($this->returnValue(false));
        $guesser3->expects($this->any())->method('supportAttribute')->will($this->returnValue(true));

        $guesser1->expects($this->once())->method('guessConstraints')->will($this->returnValue(array('foo')));
        $guesser2->expects($this->never())->method('guessConstraints');
        $guesser3->expects($this->once())->method('guessConstraints')->will($this->returnValue(array('bar')));

        $this->target->addConstraintGuesser($guesser1);
        $this->target->addConstraintGuesser($guesser2);
        $this->target->addConstraintGuesser($guesser3);

        $this->assertEquals(array('foo', 'bar'), $this->target->guessConstraints($this->getAttributeMock()));
    }
}
