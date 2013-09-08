<?php

namespace Pim\Bundle\CatalogBundle\Tests\Unit\Validator\ConstraintGuesser;

use Pim\Bundle\CatalogBundle\Validator\ConstraintGuesser\EmailGuesser;

/**
 * @author    Gildas Quemener <gildas.quemener@gmail.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class EmailGuesserTest extends ConstraintGuesserTest
{
    public function setUp()
    {
        $this->target = new EmailGuesser();
    }

    public function testInstanceOfContraintGuesserInterface()
    {
        $this->assertInstanceOf(
            'Oro\Bundle\FlexibleEntityBundle\Form\Validator\ConstraintGuesserInterface',
            $this->target
        );
    }

    public function testSupportTextAttribute()
    {
        $this->assertTrue(
            $this->target->supportAttribute(
                $this->getAttributeMock(array('attributeType' => 'pim_catalog_text',))
            )
        );
    }

    public function testGuessEmailConstraint()
    {
        $constraints = $this->target->guessConstraints(
            $this->getAttributeMock(array('attributeType'  => 'pim_catalog_text','validationRule' => 'email',))
        );

        $this->assertContainsInstanceOf('Symfony\Component\Validator\Constraints\Email', $constraints);
    }

    public function testDoNotGuessEmailConstraint()
    {
        $constraints = $this->target->guessConstraints(
            $this->getAttributeMock(array('attributeType'  => 'pim_catalog_text','validationRule' => null,))
        );

        $this->assertEquals(0, count($constraints));
    }
}
