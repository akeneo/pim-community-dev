<?php

namespace Akeneo\Tool\Component\Localization\Model;

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
     * @param string|null $locale
     *
     * @return AbstractTranslation
     */
    public function getTranslation(?string $locale = null);

    /**
     * Add translation
     *
     * @param TranslationInterface $translation
     *
     * @return TranslatableInterface
     */
    public function addTranslation(TranslationInterface $translation);

    /**
     * Remove translation
     *
     * @param TranslationInterface $translation
     *
     * @return TranslatableInterface
     */
    public function removeTranslation(TranslationInterface $translation);

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
     * @return TranslatableInterface
     */
    public function setLocale($locale);
}
