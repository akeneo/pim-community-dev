<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Twig;

use Twig\TwigFunction;

/**
 * Twig extension to manage attribute from twig templates
 *
 * @author    JM Leroux <jean-marie.leroux@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AttributeExtension extends \Twig\Extension\AbstractExtension
{
    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return [
            new TwigFunction('contains_han_characters', [$this, 'containsHanCharacters']),
        ];
    }

    /**
     * Test an attribute content for Han (Chinese, Japanese or Korean) characters
     *
     * @see https://en.wikipedia.org/wiki/Han_unification
     */
    public function containsHanCharacters(?string $text): bool
    {
        if (null === $text) {
            return false;
        }

        return 1 === preg_match("/\p{Han}+/u", $text);
    }
}
