<?php

namespace Pim\Bundle\TranslationBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;

/**
 * Translatable interface, must be implemented by translatable business objects
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface TranslatableInterface
{
    /**
     * Get translations
     *
     * @return ArrayCollection
     */
    public function getTranslations();

    /**
     * Get translation for current locale
     *
     * @return \Pim\Bundle\TranslationBundle\Entity\AbstractTranslation
     */
    public function getTranslation();

    /**
     * Add translation
     *
     * @param AbstractTranslation $translation
     *
     * @return \Pim\Bundle\TranslationBundle\Entity\TranslatableInterface
     */
    public function addTranslation(AbstractTranslation $translation);

    /**
     * Remove translation
     *
     * @param AbstractTranslation $translation
     *
     * @return \Pim\Bundle\TranslationBundle\Entity\TranslatableInterface
     */
    public function removeTranslation(AbstractTranslation $translation);

    /**
     * Get translation full qualified class name
     *
     * @return string
     */
    public function getTranslationFQCN();

    /**
     * Set the locale used for translation
     *
     * @param string $locale
     *
     * @return \Pim\Bundle\TranslationBundle\Entity\TranslatableInterface
     */
    public function setLocale($locale);
}
