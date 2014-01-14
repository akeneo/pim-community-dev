<?php

namespace Pim\Bundle\TranslationBundle\Tests\Unit\Factory;

use Pim\Bundle\TranslationBundle\Factory\TranslationFactory;

/**
 * Test related class
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class TranslationFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function itShouldCreateATranslation()
    {
        $target = $this->getTargetedClass(
            'Pim\Bundle\TranslationBundle\Tests\Entity\ItemTranslation',
            'Pim\Bundle\TranslationBundle\Tests\Entity\Item',
            'bar'
        );
        $translation = $target->createTranslation('fr_FR');

        $this->assertInstanceOf('Pim\Bundle\TranslationBundle\Tests\Entity\ItemTranslation', $translation);
        $this->assertEquals('fr_FR', $translation->getLocale());
    }

    /**
     * @test
     * @expectedException Exception
     */
    public function itShouldThrowAnExceptionIfTheTranslationIsNotAnAbstractTranslationClass()
    {
        $this->getTargetedClass(
            'Pim\Bundle\TranslationBundle\Tests\Entity\InvalidTranslation',
            'Pim\Bundle\TranslationBundle\Tests\Entity\Item',
            'bar'
        );
    }

    /**
     * @param string $translationClass
     * @param string $entityClass
     * @param string $field
     *
     * @return \Pim\Bundle\TranslationBundle\Factory\TranslationFactory
     */
    public function getTargetedClass($translationClass, $entityClass, $field)
    {
        return new TranslationFactory($translationClass, $entityClass, $field);
    }
}
