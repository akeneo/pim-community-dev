<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\AssetManager\Domain\Model\AssetFamily\RuleTemplate;

use Akeneo\AssetManager\Domain\Query\Asset\PropertyAccessibleAsset;

/**
 * @author    Christophe Chausseray <christophe.chausseray@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 */
class ReplacePattern
{
    public const PATTERN_REGEXP = '#{{(.*?)}}#';

    /**
     * This method helps figuring out if some value is an extrapolation. This means, that this value is a pattern and it
     * can be replaced by a value in a PropertyAccessibleAsset when executing a product link rule.
     *
     * For more, see the api documentation on "Asset values extrapolation".
     */
    public static function isExtrapolation($someValue): bool
    {
        return !empty(self::detectPatterns($someValue));
    }

    /**
     * @return array|string
     * @throws \InvalidArgumentException When the rule value has more than one pattern to replace and the asset value is an array
     *
     */
    public static function replace($ruleValue, PropertyAccessibleAsset $propertyAccessibleAsset)
    {
        $patterns = self::detectPatterns($ruleValue);
        $valueForPatterns = self::findValuesForPatterns($propertyAccessibleAsset, $patterns);

        return self::replacePatterns($ruleValue, $valueForPatterns);
    }

    public static function detectPatterns($ruleValue): array
    {
        if (is_bool($ruleValue)) {
            return [];
        }

        if (is_array($ruleValue)) {
            $result = [];
            foreach ($ruleValue as $item) {
                if (!is_string($item)) {
                    continue;
                }
                preg_match_all(self::PATTERN_REGEXP, $item, $matchedPatterns);
                $matchedPatterns = $matchedPatterns[1];
                $result = array_merge($result, $matchedPatterns);
            }

            return $result;
        }

        preg_match_all(self::PATTERN_REGEXP, $ruleValue, $matchedPatterns);

        return $matchedPatterns[1];
    }

    private static function findValuesForPatterns(
        PropertyAccessibleAsset $propertyAccessibleAsset,
        array $patterns
    ): array {
        $valueForPatterns = [];
        foreach ($patterns as $pattern) {
            $assetValue = self::value($propertyAccessibleAsset, $pattern);
            if (is_array($assetValue) && 1 < count($patterns)) {
                throw new \InvalidArgumentException(
                    sprintf('The asset property "%s" could not be replaced as his value is an array',
                        trim($pattern)
                    )
                );
            }
            $valueForPatterns[$pattern] = $assetValue;
        }

        return $valueForPatterns;
    }

    private static function value(PropertyAccessibleAsset $propertyAccessibleAsset, $pattern)
    {
        if (!$propertyAccessibleAsset->hasValue(trim($pattern))) {
            throw new \InvalidArgumentException(sprintf('The asset property "%s" does not exist', trim($pattern)));
        }

        return $propertyAccessibleAsset->getValue(trim($pattern));
    }

    /**
     * Recursive function that replaces every pattern for a value.
     *
     * It leverages the valueForPatterns array which holds a mapping between a pattern and the value for this array.
     */
    private static function replacePatterns($ruleValue, array $valueForPatterns)
    {
        $result = $ruleValue;
        foreach ($valueForPatterns as $pattern => $assetValue) {
            if (is_array($ruleValue)) {
                $replacedValue = [];
                foreach ($ruleValue as $value) {
                    $replacedValue[] = self::replacePatterns($value, $valueForPatterns);
                }
                $result = $replacedValue;
            } elseif (is_array($assetValue)) {
                $result = $assetValue;
            } else {
                $result = str_replace(sprintf('{{%s}}', $pattern), $assetValue, $result);
            }
        }

        return $result;
    }
}
