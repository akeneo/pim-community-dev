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

namespace Akeneo\AssetManager\Common\Fake;

use Akeneo\AssetManager\Domain\Model\Asset\AssetCode;
use Akeneo\AssetManager\Domain\Query\Asset\FindPropertyAccessibleAssetInterface;
use Akeneo\AssetManager\Domain\Query\Asset\PropertyAccessibleAsset;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\Test\Acceptance\Common\NotImplementedException;

/**
 * @author    Christophe Chausseray <christophe.chausseray@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 */
class InMemoryFindPropertyAccessibleAsset implements FindPropertyAccessibleAssetInterface
{
    /**
     * {@inheritdoc}
     */
    public function find(AssetFamilyIdentifier $assetFamilyIdentifier, AssetCode $assetCode): ?PropertyAccessibleAsset
    {
        throw new NotImplementedException('find');
    }
}
