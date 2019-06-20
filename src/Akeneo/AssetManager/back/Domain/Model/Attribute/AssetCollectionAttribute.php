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

namespace Akeneo\AssetManager\Domain\Model\Attribute;

use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Model\LabelCollection;

/**
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2018 Akeneo SAS (https://www.akeneo.com)
 */
class AssetCollectionAttribute extends AbstractAttribute
{
    public const ATTRIBUTE_TYPE = 'asset_collection';

    /** @var AssetFamilyIdentifier */
    private $assetType;

    protected function __construct(
        AttributeIdentifier $identifier,
        AssetFamilyIdentifier $assetFamilyIdentifier,
        AttributeCode $code,
        LabelCollection $labelCollection,
        AttributeOrder $order,
        AttributeIsRequired $isRequired,
        AttributeValuePerChannel $valuePerChannel,
        AttributeValuePerLocale $valuePerLocale,
        AssetFamilyIdentifier $assetType
    ) {
        parent::__construct(
            $identifier,
            $assetFamilyIdentifier,
            $code,
            $labelCollection,
            $order,
            $isRequired,
            $valuePerChannel,
            $valuePerLocale
        );

        $this->assetType = $assetType;
    }

    public static function create(
        AttributeIdentifier $identifier,
        AssetFamilyIdentifier $assetFamilyIdentifier,
        AttributeCode $code,
        LabelCollection $labelCollection,
        AttributeOrder $order,
        AttributeIsRequired $isRequired,
        AttributeValuePerChannel $valuePerChannel,
        AttributeValuePerLocale $valuePerLocale,
        AssetFamilyIdentifier $assetType
    ): self {
        return new self(
            $identifier,
            $assetFamilyIdentifier,
            $code,
            $labelCollection,
            $order,
            $isRequired,
            $valuePerChannel,
            $valuePerLocale,
            $assetType
        );
    }

    public function normalize(): array
    {
        return array_merge(
            parent::normalize(),
            [
                'asset_type' => $this->assetType->normalize()
            ]
        );
    }

    public function getAssetType(): AssetFamilyIdentifier
    {
        return $this->assetType;
    }

    public function setAssetType(AssetFamilyIdentifier $assetType): void
    {
        $this->assetType = $assetType;
    }

    public function getType(): string
    {
        return self::ATTRIBUTE_TYPE;
    }
}
