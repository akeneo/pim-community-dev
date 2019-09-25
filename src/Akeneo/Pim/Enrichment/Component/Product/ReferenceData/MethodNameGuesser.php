<?php

namespace Akeneo\Pim\Enrichment\Component\Product\ReferenceData;

use Symfony\Component\Inflector\Inflector;

/**
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MethodNameGuesser
{
    /**
     * Guess the method name for the given $prefix and $dataname.
     * $dataName should be the camelcase name of relation.
     * If $singularify is set to true, method name will be singularized.
     * Examples:
     *
     * guessProductValueMethodName('set', 'colors', true)
     *      => 'setColor'
     * guessProductValueMethodName('get', 'smoothFabrics')
     *      => 'getSmoothFabrics'
     *
     * @param string $prefix
     * @param string $dataName
     * @param bool   $singularify
     *
     * @throws \LogicException If it can't singularify a word.
     *
     * @return string
     */
    public static function guess($prefix, $dataName, $singularify = false)
    {
        $name = $dataName;

        if ($singularify) {
            $name = Inflector::singularize($dataName);

            if (is_array($name)) {
                throw new \LogicException(
                    sprintf('Error while guessing the method name for "%s"', $dataName)
                );
            }
        }

        $name = ucfirst($name);

        return sprintf('%s%s', $prefix, $name);
    }
}
