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
class ReplacePatterns
{
    public static function replace(string $ruleValue, PropertyAccessibleAsset $propertyAccessibleAsset): string
    {
        preg_match_all('#{{(.*?)}}#', $ruleValue, $matchedPatterns);

        foreach ($matchedPatterns[1] as $pattern) {
            if (!$propertyAccessibleAsset->hasValue(trim($pattern))) {
                continue;
            }

            $assetValue = $propertyAccessibleAsset->getValue(trim($pattern));
            if (is_array($assetValue)) {
                $assetValue = implode(',', $assetValue);
            }

            $ruleValue = str_replace(sprintf('{{%s}}', $pattern), $assetValue, $ruleValue);
        }

        return $ruleValue;
    }
}
