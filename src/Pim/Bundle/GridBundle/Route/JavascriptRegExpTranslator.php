<?php

namespace Pim\Bundle\GridBundle\Route;

use Pim\Bundle\GridBundle\Exception\JavascriptRegexpTranslatorException;

/**
 * Translates a php grid route regexp in a javascript regexp
 *
 * @author    Antoine Guigan <antoine@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class JavascriptRegExpTranslator
{
    /**
     * Replacement regexps
     *
     * @var array
     */
    protected $replacements = array(
        '/#(.+)#.*/'             => '\1',        // Removes regex boundaries and modifier
        '/^\^/'                  => '^%prefix%', // Adds the prefix at the start of the regex
        '#/#'                    => '\\/',       // Escapes slashes
        '/\(\?(:|P<[a-z_]+>)/i'  => '(',         // Replaces non capturing and named groups by simple groups
    );

    /**
     * Unsupported regexp features regexps
     *
     * @var array
     */
    protected $unsupported = array(
        '/\+\+/',                                // Greedy + operator
        '/\(\?<?[=!]/',                          // Assertions
    );

    /**
     * Translates a PHP regexp to Javascript
     *
     * @param string $regexp
     * @param array  $parameterValues
     * 
     * @return string
     * @throws JavascriptRegexpTranslatorException
     */
    public function translate($regexp, array $parameterValues = array())
    {
        foreach ($parameterValues as $name => $value) {
        $regexp = preg_replace(
                sprintf('/\(\?P<%s>([^()]|(?<group>\(((?>[^()]+)|(?&group))*\)))+\)/', $name),
                $value,
                $regexp
            );
        }
        foreach ($this->unsupported as $unsupportedRegexp) {
            if (preg_match($unsupportedRegexp, $regexp)) {
                throw new JavascriptRegexpTranslatorException();
            }
        }
        return sprintf(
            '/%s/',
            preg_replace(
                array_keys($this->replacements),
                array_values($this->replacements),
                $regexp
            )
        );
    }
}
