<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Twig;

use Twig_Extension;
use Twig_SimpleFunction;

/**
 * Twig extension to manage attribute from twig templates
 *
 * @author    JM Leroux <jean-marie.leroux@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AttributeExtension extends Twig_Extension
{
    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return [
            new Twig_SimpleFunction('contains_asian_characters', [$this, 'containsAsianCharacters']),
        ];
    }

    /**
     * Test an attribute content for Asian characters
     */
    public function containsAsianCharacters(?string $text): bool
    {
        if (null === $text) {
            return false;
        }

        return 1 === preg_match("/\p{Han}+/u", $text);
    }
}
