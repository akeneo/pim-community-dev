<?php
namespace Oro\Bundle\FlexibleEntityBundle\Model\Behavior;

/**
 * Translatable interface, implemented by class which can be translated
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
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
