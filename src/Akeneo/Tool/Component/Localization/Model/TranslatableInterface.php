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
     */
    public function getTranslations(): ArrayCollection;

    /**
     * Get translation for current locale
     *
     * @param string|null $locale
     */
    public function getTranslation(?string $locale = null): AbstractTranslation;

    /**
     * Add translation
     *
     * @param TranslationInterface $translation
     */
    public function addTranslation(TranslationInterface $translation): \Akeneo\Tool\Component\Localization\Model\TranslatableInterface;

    /**
     * Remove translation
     *
     * @param TranslationInterface $translation
     */
    public function removeTranslation(TranslationInterface $translation): \Akeneo\Tool\Component\Localization\Model\TranslatableInterface;

    /**
     * Get translation full qualified class name
     */
    public function getTranslationFQCN(): string;

    /**
     * Set the locale used for translation
     *
     * @param string $locale
     */
    public function setLocale(string $locale): \Akeneo\Tool\Component\Localization\Model\TranslatableInterface;
}
