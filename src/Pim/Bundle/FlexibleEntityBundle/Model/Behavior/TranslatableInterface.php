<?php

namespace Pim\Bundle\FlexibleEntityBundle\Model\Behavior;

/**
 * Translatable interface, implemented by class which can be translated
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
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
