<?php

namespace Pim\Bundle\ProductBundle\Tests\Unit\Validator\ConstraintGuesser;

use Pim\Bundle\ProductBundle\Validator\ConstraintGuesser\UrlGuesser;

/**
 * @author    Gildas Quemener <gildas.quemener@gmail.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class UrlGuesserTest extends ConstraintGuesserTest
{
    public function setUp()
    {
        $this->target = new UrlGuesser;
    }

    public function testInstanceOfContraintGuesserInterface()
    {
        $this->assertInstanceOf('Oro\Bundle\FlexibleEntityBundle\Form\Validator\ConstraintGuesserInterface', $this->target);
    }

    public function testSupportAttribute()
    {
        $this->assertTrue(
            $this->target->supportAttribute(
                $this->getAttributeMock(array('attributeType' => 'pim_product_text',))
            )
        );
    }

    public function testGuessUrlConstraint()
    {
        $constraints = $this->target->guessConstraints(
            $this->getAttributeMock(
                array(
                    'attributeType'  => 'pim_product_text',
                    'validationRule' => 'url',
                )
            )
        );

        $this->assertContainsInstanceOf('Symfony\Component\Validator\Constraints\Url', $constraints);
    }

    public function testDoNotGuessUrlConstraint()
    {
        $constraints = $this->target->guessConstraints(
            $this->getAttributeMock(
                array(
                    'attributeType'  => 'pim_product_text',
                    'validationRule' => null,
                )
            )
        );

        $this->assertEquals(0, count($constraints));
    }
}
