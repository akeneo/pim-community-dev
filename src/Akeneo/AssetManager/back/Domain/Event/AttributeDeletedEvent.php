<?php

declare(strict_types=1);

namespace Akeneo\AssetManager\Domain\Event;

use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeIdentifier;
use Symfony\Component\EventDispatcher\Event;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @internal
 */
class AttributeDeletedEvent extends Event
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
