<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Spellcheck\Filter;

/**
 * @license   https://opensource.org/licenses/MIT MIT
 * @source    https://github.com/mekras/php-speller
 */
class HTMLFilter
{
    /**
     * Attribute name context.
     */
    public const CTX_ATTR_NAME = 'attr_name';

    /**
     * Attribute value context.
     */
    public const CTX_ATTR_VALUE = 'attr_value';

    /**
     * Tag attributes context.
     */
    public const CTX_TAG_ATTRS = 'tag_attrs';

    /**
     * Tag content context.
     */
    public const CTX_TAG_CONTENT = 'tag_content';

    /**
     * Tag name context.
     */
    public const CTX_TAG_NAME = 'tag_name';

    /**
     * Ignore content of these tags.
     */
    private static array $ignoreTags = [
        'script'
    ];

    /**
     * Attrs with text contents.
     */
    private static array $textAttrs = [
        'abbr',
        'alt',
        'content',
        'label',
        'placeholder',
        'title'
    ];

    /**
     * Meta tag names with text content.
     */
    private static array $textMetaTags = [
        'description',
        'keywords'
    ];

    public function __construct()
    {
    }

    /**
     * Filter string.
     */
    public function filter(string $string): string
    {
        $result = '';

        $string = $this->filterEntities($string);
        $string = $this->filterMetaTags($string);

        // Current/last tag name
        $tagName = null;
        // Current/last attribute name
        $attrName = null;
        // Current context
        $context = self::CTX_TAG_CONTENT;
        // Expected context
        $expecting = null;

        // By default tag content treated as text.
        $ignoreTagContent = false;
        // By default attribute values NOT treated as text.
        $ignoreAttrValue = true;

        $length = mb_strlen($string);
        for ($i = 0; $i < $length; $i++) {
            $char = mb_substr($string, $i, 1);
            switch (true) {
                case '<' === $char:
                    $context = self::CTX_TAG_NAME;
                    $tagName = null;
                    $char = ' ';
                    break;

                case '>' === $char:
                    if ($this->isIgnoredTag($tagName)) {
                        $ignoreTagContent = true;
                    } elseif ($tagName === null || '/' === $tagName[0]) {
                        $ignoreTagContent = false; // Restore to default state.
                    }
                    $context = self::CTX_TAG_CONTENT;
                    $expecting = null;
                    $char = ' ';
                    break;

                case ' ' === $char:
                case "\n" === $char:
                case "\t" === $char:
                    switch ($context) {
                        case self::CTX_ATTR_NAME:
                        case self::CTX_TAG_NAME:
                            $context = self::CTX_TAG_ATTRS;
                            break;
                    }
                    break;

                case '=' === $char && (self::CTX_ATTR_NAME === $context || self::CTX_TAG_ATTRS === $context):
                    $expecting = self::CTX_ATTR_VALUE;
                    $char = ' ';
                    break;

                case '"' === $char:
                case "'" === $char:
                    switch (true) {
                        case self::CTX_ATTR_VALUE === $expecting:
                            $context = self::CTX_ATTR_VALUE;
                            if ($attrName !== null) {
                                $ignoreAttrValue = !in_array(strtolower($attrName), self::$textAttrs, true);
                            }
                            $expecting = null;
                            break;

                        case self::CTX_ATTR_VALUE === $context:
                            $context = self::CTX_TAG_ATTRS;
                            break;
                    }
                    $char = ' ';
                    break;

                default:
                    switch ($context) {
                        case self::CTX_TAG_NAME:
                            $tagName .= $char;
                            $char = ' ';
                            break;

                        case self::CTX_TAG_ATTRS:
                            $context = self::CTX_ATTR_NAME;
                            $attrName = null;
                            // no break needed
                        case self::CTX_ATTR_NAME:
                            $attrName .= $char;
                            $char = ' ';
                            break;

                        case self::CTX_ATTR_VALUE:
                            if ($ignoreAttrValue) {
                                $char = ' ';
                            }
                            break;

                        case self::CTX_TAG_CONTENT:
                            if ($ignoreTagContent) {
                                $char = ' ';
                            }
                            break;
                    }
            }
            $result .= $char;
        }

        return $result;
    }

    /**
     * Replace HTML entities.
     */
    private function filterEntities(string $string): string
    {
        return preg_replace_callback(
            '/&\w+;/',
            static function ($match) {
                return str_repeat(' ', strlen($match[0]));
            },
            $string
        );
    }

    /**
     * Replace non-text meta tags.
     */
    private function filterMetaTags(string $string): string
    {
        return preg_replace_callback(
            '/<meta[^>]+(http-equiv\s*=|name\s*=\s*["\']?([^>"\']+))[^>]*>/i',
            static function ($match) {
                if (
                    count($match) < 3
                    || !in_array(strtolower($match[2]), self::$textMetaTags, true)
                ) {
                    return str_repeat(' ', strlen($match[0]));
                }

                return $match[0];
            },
            $string
        );
    }

    /**
     * Return true if $tagName is in the list of ignored tags.
     */
    private function isIgnoredTag(?string $tagName): bool
    {
        if ($tagName === null) {
            return false;
        }

        foreach (self::$ignoreTags as $tag) {
            if (strcasecmp($tag, $tagName) === 0) {
                return true;
            }
        }

        return false;
    }
}
