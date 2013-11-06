<?php

namespace Pim\Bundle\FlexibleEntityBundle\Model\Behavior;

/**
 * Translatable interface, implemented by class which can be translated
 *
 *
 */
interface TranslatableInterface
{
    /**
     * Get used locale
     * @return string $locale
     */
    public function getLocale();

    /**
     * Set used locale
     * @param string $locale
     *
     * @return TranslatableInterface
     */
    public function setLocale($locale);
}
