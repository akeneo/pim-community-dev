<?php
namespace Pim\Bundle\TranslationBundle\Tests\Entity;

use Doctrine\Common\Collections\ArrayCollection;

use Gedmo\Translatable\Translatable;

use Gedmo\Mapping\Annotation as Gedmo;

/**
 * Test class
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @Gedmo\TranslationEntity(class="Pim\Bundle\TranslationBundle\Tests\Entity\ItemTranslation")
 */
class Item implements Translatable
{

    /**
     * @var string $name
     */
    protected $name;

    /**
     * Used locale
     *
     * @var string $locale
     *
     * @Gedmo\Locale
     */
    protected $locale;

    /**
     * @var ArrayCollection $translations
     */
    protected $translations;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->translations = new ArrayCollection();
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return Item
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Define locale used by entity
     *
     * @param string $locale
     *
     * @return Item
     */
    public function setTranslatableLocale($locale)
    {
        $this->locale = $locale;

        return $this;
    }

    /**
     * Get translations
     *
     * @return ArrayCollection
     */
    public function getTranslations()
    {
        return $this->translations;
    }

    /**
     * Add translation
     *
     * @param ItemTranslation $translation
     *
     * @return Item
     */
    public function addTranslation(ItemTranslation $translation)
    {
        if (!$this->translations->contains($translation)) {
            $this->translations->add($translation);
        }

        return $this;
    }

    /**
     * Remove translation
     *
     * @param ItemTranslation $translation
     *
     * @return Item
     */
    public function removeTranslation(ItemTranslation $translation)
    {
        $this->translations->removeElement($translation);

        return $this;
    }
}
