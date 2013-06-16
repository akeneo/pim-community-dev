<?php

namespace Pim\Bundle\TranslationBundle\Tests\Unit\Entity;

use Pim\Bundle\TranslationBundle\Tests\Entity\ItemTranslation;
use Pim\Bundle\TranslationBundle\Tests\Entity\Item;

/**
 * Test related class
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class AbstractTranslatableEntityTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test related method
     */
    public function testConstruct()
    {
        $entity = $this->createTranslatableItem();

        $this->assertEntity($entity);
        $this->assertInstanceOf('\Doctrine\Common\Collections\Collection', $entity->getTranslations());
        $this->assertCount(0, $entity->getTranslations());
    }

    /**
     * Test related method
     * Just a call to prevent fatal errors (no way to verify value is set)
     */
    public function testSetTranslatableLocale()
    {
        $entity = $this->createTranslatableItem();
        $entity->setTranslatableLocale('en_US');
    }

    /**
     * Test related get/add/remove methods
     */
    public function testTranslations()
    {
        $entity = $this->createTranslatableItem();
        $this->assertCount(0, $entity->getTranslations());

        $translation = $this->createItemTranslation();
        $this->assertEntity($entity->addTranslation($translation));
        $this->assertCount(1, $entity->getTranslations());
        $this->assertTranslation($entity->getTranslations()->first());

        $this->assertEntity($entity->removeTranslation($translation));
        $this->assertCount(0, $entity->getTranslations());
    }

    /**
     * Create a translatable entity mock
     *
     * @return Pim\Bundle\TranslationBundle\Tests\Entity\Item
     */
    protected function createTranslatableItem()
    {
        return new Item();
    }

    /**
     * Create a translation entity mock
     *
     * @return Pim\Bundle\TranslationBundle\Tests\Entity\ItemTranslation
     */
    protected function createItemTranslation()
    {
        return new ItemTranslation();
    }

    /**
     * Assert translatable entity
     *
     * @param Item $entity
     */
    protected function assertEntity($entity)
    {
        $this->assertInstanceOf('Pim\Bundle\TranslationBundle\Tests\Entity\Item', $entity);
    }

    /**
     * Assert translation
     *
     * @param ItemTranslation $translation
     */
    protected function assertTranslation($translation)
    {
        $this->assertInstanceOf('Pim\Bundle\TranslationBundle\Tests\Entity\ItemTranslation', $translation);
    }
}
