<?php

namespace Pim\Bundle\CatalogBundle\Tests\Unit\Validator\ConstraintGuesser;

use Pim\Bundle\CatalogBundle\Validator\ConstraintGuesser\FileGuesser;

/**
 * Test related class
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FileGuesserTest extends ConstraintGuesserTest
{
    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->target = new FileGuesser();
    }

    /**
     * Test related method
     */
    public function testInstanceOfContraintGuesserInterface()
    {
        $this->assertInstanceOf(
            'Oro\Bundle\FlexibleEntityBundle\Form\Validator\ConstraintGuesserInterface',
            $this->target
        );
    }

    /**
     * Test related method
     */
    public function testSupportAttribute()
    {
        $this->assertTrue(
            $this->target->supportAttribute($this->getAttributeMock(array('attributeType' => 'pim_catalog_file')))
        );

        $this->assertTrue(
            $this->target->supportAttribute($this->getAttributeMock(array('attributeType' => 'pim_catalog_image')))
        );
    }

    /**
     * Test related method
     */
    public function testGuessFileMaxSizeConstraint()
    {
        $maxSize = 5.5;
        $constraints = $this->target->guessConstraints(
            $this->getAttributeMock(array('attributeType' => 'pim_catalog_file', 'maxFileSize' => $maxSize))
        );

        $this->assertContainsInstanceOf('Pim\Bundle\CatalogBundle\Validator\Constraints\File', $constraints);
        $this->assertConstraintsConfiguration(
            'Pim\Bundle\CatalogBundle\Validator\Constraints\File',
            $constraints,
            array('maxSize' => $maxSize * 1024 . 'k')
        );
    }

    /**
     * Test related method
     */
    public function testGuessAllowedExtensionConstraint()
    {
        $constraints = $this->target->guessConstraints(
            $this->getAttributeMock(
                array(
                    'attributeType'     => 'pim_catalog_file',
                    'allowedExtensions' => array('gif', 'jpg'),
                )
            )
        );

        $this->assertContainsInstanceOf('Pim\Bundle\CatalogBundle\Validator\Constraints\File', $constraints);
        $this->assertConstraintsConfiguration(
            'Pim\Bundle\CatalogBundle\Validator\Constraints\File',
            $constraints,
            array('allowedExtensions' => array('gif', 'jpg'))
        );
    }

    /**
     * Test related method
     */
    public function testGuessMultipleFileConstraints()
    {
        $constraints = $this->target->guessConstraints(
            $this->getAttributeMock(
                array(
                    'attributeType'     => 'pim_catalog_file',
                    'maxFileSize'       => 5,
                    'allowedExtensions' => array('gif', 'jpg'),
                )
            )
        );

        $this->assertContainsInstanceOf('Pim\Bundle\CatalogBundle\Validator\Constraints\File', $constraints);
        $this->assertConstraintsConfiguration(
            'Pim\Bundle\CatalogBundle\Validator\Constraints\File',
            $constraints,
            array('maxSize' => '5M', 'allowedExtensions' => array('gif', 'jpg'))
        );
    }
}
