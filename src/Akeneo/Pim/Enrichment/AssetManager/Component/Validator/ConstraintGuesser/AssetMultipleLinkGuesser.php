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

namespace Akeneo\Pim\Enrichment\AssetManager\Component\Validator\ConstraintGuesser;

use Akeneo\Pim\Enrichment\AssetManager\Component\Validator\Constraints\AssetsShouldBelongToAssetFamily;
use Akeneo\Pim\Enrichment\AssetManager\Component\Validator\Constraints\ThereShouldBeLessAssetsInValueThanLimit;
use Akeneo\Pim\Enrichment\Component\Product\Validator\ConstraintGuesserInterface;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;

/**
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2019 Akeneo SAS (https://www.akeneo.com)
 */
class AssetMultipleLinkGuesser implements ConstraintGuesserInterface
{
    public function supportAttribute(AttributeInterface $attribute)
    {
        return in_array(
            $attribute->getType(),
            [
                AttributeTypes::ASSET_COLLECTION,
            ]
        );
    }

    public function guessConstraints(AttributeInterface $attribute)
    {
        return [
            new AssetsShouldBelongToAssetFamily(),
            new ThereShouldBeLessAssetsInValueThanLimit(),
        ];
    }
}
