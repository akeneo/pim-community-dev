<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\AssetManager\Domain\Event;

use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Symfony\Component\EventDispatcher\Event;

/**
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2019 Akeneo SAS (https://www.akeneo.com)
 * @internal  This event is not part of the public API and you should not rely on it for custom purpose.
 */
class AssetFamilyCreatedEvent extends Event
{
    private AssetFamilyIdentifier $assetFamilyIdentifier;

    public function __construct(AssetFamilyIdentifier $assetFamilyIdentifier)
    {
        $this->assetFamilyIdentifier = $assetFamilyIdentifier;
    }

    public function getAssetFamilyIdentifier(): AssetFamilyIdentifier
    {
        return $this->assetFamilyIdentifier;
    }
}
