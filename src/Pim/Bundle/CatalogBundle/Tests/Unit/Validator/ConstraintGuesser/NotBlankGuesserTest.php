<?php

namespace Pim\Bundle\CatalogBundle\Tests\Unit\Validator\ConstraintGuesser;

use Pim\Bundle\CatalogBundle\Validator\ConstraintGuesser\NotBlankGuesser;

/**
 * @author    Gildas Quemener <gildas.quemener@gmail.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class NotBlankGuesserTest extends ConstraintGuesserTest
{
    public function setUp()
    {
        $this->target = new NotBlankGuesser();
    }

    public function testInstanceOfContraintGuesserInterface()
    {
        $this->assertInstanceOf(
            'Oro\Bundle\FlexibleEntityBundle\Form\Validator\ConstraintGuesserInterface',
            $this->target
        );
    }

    public function testSupportAnyAttribute()
    {
        $this->assertTrue($this->target->supportAttribute($this->getAttributeMock()));
    }

    public function testGuessNotBlankConstraint()
    {
        $this->assertContainsInstanceOf(
            'Symfony\Component\Validator\Constraints\NotBlank',
            $this->target->guessConstraints($this->getAttributeMock(array('required' => true,)))
        );
    }

    public function testDoNotGuessNotBlankConstraint()
    {
        $this->assertEquals(0, count($this->target->guessConstraints($this->getAttributeMock())));
    }
}
