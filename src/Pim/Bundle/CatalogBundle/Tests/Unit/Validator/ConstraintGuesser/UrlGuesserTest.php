<?php

namespace Pim\Bundle\CatalogBundle\Tests\Unit\Validator\ConstraintGuesser;

use Pim\Bundle\CatalogBundle\Validator\ConstraintGuesser\UrlGuesser;

/**
 * Test related class
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class UrlGuesserTest extends ConstraintGuesserTest
{
    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->target = new UrlGuesser();
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
            $this->target->supportAttribute(
                $this->getAttributeMock(array('attributeType' => 'pim_catalog_text'))
            )
        );
    }

    /**
     * Test related method
     */
    public function testGuessUrlConstraint()
    {
        $constraints = $this->target->guessConstraints(
            $this->getAttributeMock(
                array(
                    'attributeType'  => 'pim_catalog_text',
                    'validationRule' => 'url',
                )
            )
        );

        $this->assertContainsInstanceOf('Symfony\Component\Validator\Constraints\Url', $constraints);
    }

    /**
     * Test related method
     */
    public function testDoNotGuessUrlConstraint()
    {
        $constraints = $this->target->guessConstraints(
            $this->getAttributeMock(
                array(
                    'attributeType'  => 'pim_catalog_text',
                    'validationRule' => null,
                )
            )
        );

        $this->assertEquals(0, count($constraints));
    }
}
