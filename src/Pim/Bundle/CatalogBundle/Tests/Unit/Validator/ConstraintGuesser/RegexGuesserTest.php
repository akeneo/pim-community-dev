<?php

namespace Pim\Bundle\CatalogBundle\Tests\Unit\Validator\ConstraintGuesser;

use Pim\Bundle\CatalogBundle\Validator\ConstraintGuesser\RegexGuesser;

/**
 * Test related class
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class RegexGuesserTest extends ConstraintGuesserTest
{
    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->target = new RegexGuesser();
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
            $this->target->supportAttribute($this->getAttributeMock(['attributeType' => 'pim_catalog_text']))
        );
    }

    /**
     * Test related method
     */
    public function testGuessRegexConstraint()
    {
        $constraints = $this->target->guessConstraints(
            $this->getAttributeMock(
                [
                    'attributeType'    => 'pim_catalog_text',
                    'validationRule'   => 'regexp',
                    'validationRegexp' => '/foo/',
                ]
            )
        );

        $this->assertContainsInstanceOf('Symfony\Component\Validator\Constraints\Regex', $constraints);
        $this->assertConstraintsConfiguration(
            'Symfony\Component\Validator\Constraints\Regex',
            $constraints,
            ['pattern' => '/foo/']
        );
    }

    /**
     * Test related method
     */
    public function testDoNotGuessRegexConstraint()
    {
        $this->assertEquals(
            0,
            count(
                $this->target->guessConstraints(
                    $this->getAttributeMock(
                        [
                            'attributeType'  => 'pim_catalog_text',
                            'validationRule' => 'regexp',
                        ]
                    )
                )
            )
        );

        $this->assertEquals(
            0,
            count(
                $this->target->guessConstraints(
                    $this->getAttributeMock(
                        [
                            'attributeType'  => 'pim_catalog_text',
                            'validationRule' => null,
                        ]
                    )
                )
            )
        );
    }
}
