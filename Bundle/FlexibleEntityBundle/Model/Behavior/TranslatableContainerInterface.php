<?php
namespace Oro\Bundle\FlexibleEntityBundle\Model\Behavior;

/**
 * Translatable container interface, implemented by class which can't be translate but contains some other translatable
 * content, for instance, a flexible entity is not translatable itself but its values can
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
 *
 */
interface TranslatableContainerInterface
{

    /**
     * Get used locale
     * @return string $locale
     */
    public function getLocaleCode();

    /**
     * Set used locale
     * @param string $locale
     *
     * @return TranslatableInterface
     */
    public function setLocaleCode($locale);

    /**
     * Get default locale code
     *
     * @return string
     */
    public function getDefaultLocaleCode();

    /**
     * Set locale code
     *
     * @param string $code
     *
     * @return TranslatableInterface
     */
    public function setDefaultLocaleCode($code);
}
