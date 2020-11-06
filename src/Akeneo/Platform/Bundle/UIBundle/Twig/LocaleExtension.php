<?php

namespace Akeneo\Platform\Bundle\UIBundle\Twig;

/**
 * Twig extension to present locales
 *
 * @author    Clement Gautier <clement.gautier@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class LocaleExtension extends \Twig_Extension
{
    /**
     * {@inheritdoc}
     */
    public function getFilters(): array
    {
        return [
            new \Twig_SimpleFilter('pretty_locale_name', fn($code) => $this->prettyLocaleName($code), ['is_safe' => ['html']]),
        ];
    }

    /**
     * Display the name of a locale by its code, like English (United States) when you provide en_US
     *
     * @param string $code
     */
    public function prettyLocaleName(string $code): string
    {
        if (empty($code)) {
            return '';
        }

        return \Locale::getDisplayName($code);
    }
}
