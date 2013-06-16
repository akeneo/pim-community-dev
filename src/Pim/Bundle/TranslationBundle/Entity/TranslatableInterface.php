<?php

namespace Pim\Bundle\TranslationBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Gedmo\Translatable\Translatable;

/**
 * Translatable interface
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
interface TranslatableInterface extends Translatable
{

    /**
     * Define locale used by entity
     *
     * @param string $locale
     *
     * @return TranslatableInterface
     */
    public function setTranslatableLocale($locale);

    /**
     * Get translations
     *
     * @return ArrayCollection
     */
    public function getTranslations();

    /**
     * Add translation
     *
     * @param \Gedmo\Translatable\Entity\MappedSuperclass\AbstractTranslation $translation
     *
     * @return TranslatableInterface
     */
    public function addTranslation(AbstractTranslation $translation);

    /**
     * Remove translation
     *
     * @param \Gedmo\Translatable\Entity\MappedSuperclass\AbstractTranslation $translation
     *
     * @return TranslatableInterface
     */
    public function removeTranslation(AbstractTranslation $translation);
}
