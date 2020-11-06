<?php

namespace Akeneo\Tool\Component\Localization\Model;

/**
 * Localizable interface, implemented by class which can be localized
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface LocalizableInterface
{
    /**
     * Get used locale
     *
     * @return string $locale
     */
    public function getLocale(): string;

    /**
     * Set used locale
     *
     * @param string $locale
     */
    public function setLocale(string $locale): \Akeneo\Tool\Component\Localization\Model\LocalizableInterface;
}
