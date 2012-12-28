<?php
namespace Oro\Bundle\DataModelBundle\Model\Behavior;

/**
 * Translatable interface
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
