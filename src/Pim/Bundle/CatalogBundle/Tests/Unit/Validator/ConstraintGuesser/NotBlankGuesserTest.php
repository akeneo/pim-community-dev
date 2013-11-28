<?php

namespace Pim\Bundle\CatalogBundle\Tests\Unit\Validator\ConstraintGuesser;

use Pim\Bundle\CatalogBundle\Validator\ConstraintGuesser\NotBlankGuesser;

/**
 * Test related class
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class NotBlankGuesserTest extends ConstraintGuesserTest
{
    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->target = new NotBlankGuesser();
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
    public function testSupportAnyAttribute()
    {
        $this->assertTrue($this->target->supportAttribute($this->getAttributeMock()));
    }

    /**
     * Test related method
     */
    public function testGuessNotBlankConstraint()
    {
        $this->assertContainsInstanceOf(
            'Symfony\Component\Validator\Constraints\NotBlank',
            $this->target->guessConstraints($this->getAttributeMock(array('required' => true)))
        );
    }

    /**
     * Test related method
     */
    public function testDoNotGuessNotBlankConstraint()
    {
        $this->assertEquals(0, count($this->target->guessConstraints($this->getAttributeMock())));
    }
}
