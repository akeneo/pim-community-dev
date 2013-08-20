<?php

namespace Pim\Bundle\ProductBundle\Tests\Unit\Validator\ConstraintGuesser;

use Pim\Bundle\ProductBundle\Validator\ConstraintGuesser\FileGuesser;

/**
 * @author    Gildas Quemener <gildas.quemener@gmail.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FileGuesserTest extends ConstraintGuesserTest
{
    public function setUp()
    {
        $this->target = new FileGuesser;
    }

    public function testInstanceOfContraintGuesserInterface()
    {
        $this->assertInstanceOf(
            'Oro\Bundle\FlexibleEntityBundle\Form\Validator\ConstraintGuesserInterface',
            $this->target
        );
    }

    public function testSupportAttribute()
    {
        $this->assertTrue(
            $this->target->supportAttribute($this->getAttributeMock(array('attributeType' => 'pim_product_file',)))
        );

        $this->assertTrue(
            $this->target->supportAttribute($this->getAttributeMock(array('attributeType' => 'pim_product_image',)))
        );
    }

    public function testGuessFileMaxSizeConstraint()
    {
        $constraints = $this->target->guessConstraints(
            $this->getAttributeMock(array('attributeType' => 'pim_product_file', 'maxFileSize'   => 5000,))
        );

        $this->assertContainsInstanceOf('Pim\Bundle\ProductBundle\Validator\Constraints\File', $constraints);
        $this->assertConstraintsConfiguration(
            'Pim\Bundle\ProductBundle\Validator\Constraints\File',
            $constraints,
            array('maxSize' => 5000,)
        );
    }

    public function testGuessAllowedExtensionConstraint()
    {
        $constraints = $this->target->guessConstraints(
            $this->getAttributeMock(
                array(
                    'attributeType'         => 'pim_product_file',
                    'allowedExtensions' => array('gif', 'jpg'),
                )
            )
        );

        $this->assertContainsInstanceOf('Pim\Bundle\ProductBundle\Validator\Constraints\File', $constraints);
        $this->assertConstraintsConfiguration(
            'Pim\Bundle\ProductBundle\Validator\Constraints\File',
            $constraints,
            array('allowedExtensions' => array('gif', 'jpg'),)
        );
    }

    public function testGuessMultipleFileConstraints()
    {
        $constraints = $this->target->guessConstraints(
            $this->getAttributeMock(
                array(
                    'attributeType'         => 'pim_product_file',
                    'maxFileSize'           => 5000,
                    'allowedExtensions' => array('gif', 'jpg'),
                )
            )
        );

        $this->assertContainsInstanceOf('Pim\Bundle\ProductBundle\Validator\Constraints\File', $constraints);
        $this->assertConstraintsConfiguration(
            'Pim\Bundle\ProductBundle\Validator\Constraints\File',
            $constraints,
            array('maxSize' => 5000, 'allowedExtensions' => array('gif', 'jpg'),)
        );
    }
}
