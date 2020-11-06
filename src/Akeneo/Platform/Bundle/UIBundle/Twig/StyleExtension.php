<?php

namespace Akeneo\Platform\Bundle\UIBundle\Twig;

/**
 * Some presentation filters
 *
 * @author    Clement Gautier <clement.gautier@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class StyleExtension extends \Twig_Extension
{
    /**
     * {@inheritdoc}
     */
    public function getFilters(): array
    {
        return [
            new \Twig_SimpleFilter('highlight', fn($content) => $this->highlight($content))
        ];
    }

    /**
     * Return a string wrapper in a span with a specific class
     *
     * @param string $content
     */
    public function highlight(string $content): string
    {
        return sprintf('<span class="AknRule-attribute">%s</span>', $content);
    }
}
