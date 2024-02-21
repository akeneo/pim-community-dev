<?php

namespace Akeneo\Platform\Bundle\UIBundle\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

/**
 * Twig extension to present locales
 *
 * @author    Clement Gautier <clement.gautier@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class LocaleExtension extends AbstractExtension
{
    /**
     * {@inheritdoc}
     */
    public function getFilters()
    {
        return [
            new TwigFilter('pretty_locale_name', [$this, 'prettyLocaleName'], ['is_safe' => ['html']]),
        ];
    }

    /**
     * Display the name of a locale by its code, like English (United States) when you provide en_US
     *
     * @param string $code
     *
     * @return string
     */
    public function prettyLocaleName($code)
    {
        if (empty($code)) {
            return '';
        }

        return \Locale::getDisplayName($code);
    }
}
