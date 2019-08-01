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
    /**
     * @throws \InvalidArgumentException When the rule value has more than one pattern to replace and the asset value is an array
     *
     * @return array|string
     */
    public static function replace(string $ruleValue, PropertyAccessibleAsset $propertyAccessibleAsset)
    {
        preg_match_all('#{{(.*?)}}#', $ruleValue, $matchedPatterns);
        $matchedPatterns = $matchedPatterns[1];

        foreach ($matchedPatterns as $pattern) {
            if (!$propertyAccessibleAsset->hasValue(trim($pattern))) {
                throw new \InvalidArgumentException(sprintf('The asset property "%s" does not exist', trim($pattern)));
            }

            $assetValue = $propertyAccessibleAsset->getValue(trim($pattern));

            if (is_array($assetValue)) {
                if (1 < count($matchedPatterns)) {
                    throw new \InvalidArgumentException(sprintf('The asset property "%s" could not be replaced as his value is an array', trim($pattern)));
                }

                return $assetValue;
            }

            $ruleValue = str_replace(sprintf('{{%s}}', $pattern), $assetValue, $ruleValue);
        }

        return $ruleValue;
    }
}
