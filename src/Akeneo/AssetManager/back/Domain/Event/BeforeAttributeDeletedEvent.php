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
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeIdentifier;
use Symfony\Component\EventDispatcher\Event;

/**
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2019 Akeneo SAS (https://www.akeneo.com)
 * @internal
 */
class BeforeAttributeDeletedEvent extends Event
{
    public AssetFamilyIdentifier $assetFamilyIdentifier;

    public AttributeIdentifier $attributeIdentifier;

    public function __construct(AssetFamilyIdentifier $assetFamilyIdentifier, AttributeIdentifier $attributeIdentifier)
    {
        $this->assetFamilyIdentifier = $assetFamilyIdentifier;
        $this->attributeIdentifier = $attributeIdentifier;
    }

    public function getAssetFamilyIdentifier(): AssetFamilyIdentifier
    {
        return $this->assetFamilyIdentifier;
    }

    public function getAttributeIdentifier(): AttributeIdentifier
    {
        return $this->attributeIdentifier;
    }
}
