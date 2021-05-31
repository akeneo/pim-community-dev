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

namespace Akeneo\AssetManager\Domain\Query\Asset;

use Akeneo\AssetManager\Domain\Model\Asset\AssetCode;
use Akeneo\AssetManager\Domain\Model\Asset\AssetIdentifier;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\AssetManager\Domain\Model\Image;
use Akeneo\AssetManager\Domain\Model\LabelCollection;

/**
 * Read model representing a asset's details.
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class AssetDetails
{
    private const IDENTIFIER = 'identifier';
    private const ASSET_FAMILY_IDENTIFIER = 'asset_family_identifier';
    private const ATTRIBUTE_AS_MAIN_MEDIA_IDENTIFIER = 'attribute_as_main_media_identifier';
    private const CODE = 'code';
    private const LABELS = 'labels';
    private const IMAGE = 'image';
    private const VALUES = 'values';
    private const PERMISSION = 'permission';
    private const EDIT_PERMISSION = 'edit';
    private const CREATED_AT = 'created_at';
    private const UPDATED_AT = 'updated_at';

    private const DATE_FORMAT = 'c';

    public AssetIdentifier $identifier;

    public AssetFamilyIdentifier $assetFamilyIdentifier;

    /** * @var AttributeIdentifier */
    public AttributeIdentifier $attributeAsMainMediaIdentifier;

    public AssetCode $code;

    public LabelCollection $labels;

    public \DateTimeImmutable $createdAt;

    public \DateTimeImmutable $updatedAt;

    public array $image;

    public array $values;

    public bool $isAllowedToEdit = true;

    public function __construct(
        AssetIdentifier $identifier,
        AssetFamilyIdentifier $assetFamilyIdentifier,
        AttributeIdentifier $attributeAsMainMediaIdentifier,
        AssetCode $code,
        LabelCollection $labels,
        \DateTimeImmutable $createdAt,
        \DateTimeImmutable $updatedAt,
        array $image,
        array $values,
        bool $isAllowedToEdit
    ) {
        $this->identifier = $identifier;
        $this->assetFamilyIdentifier = $assetFamilyIdentifier;
        $this->attributeAsMainMediaIdentifier = $attributeAsMainMediaIdentifier;
        $this->code = $code;
        $this->labels = $labels;
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;
        $this->values = $values;
        $this->image = $image;
        $this->isAllowedToEdit = $isAllowedToEdit;
    }

    public function normalize(): array
    {
        return [
            self::IDENTIFIER                  => $this->identifier->normalize(),
            self::ASSET_FAMILY_IDENTIFIER => $this->assetFamilyIdentifier->normalize(),
            self::ATTRIBUTE_AS_MAIN_MEDIA_IDENTIFIER => $this->attributeAsMainMediaIdentifier->normalize(),
            self::CODE                        => $this->code->normalize(),
            self::LABELS                      => $this->labels->normalize(),
            self::CREATED_AT => $this->createdAt->format(self::DATE_FORMAT),
            self::UPDATED_AT => $this->updatedAt->format(self::DATE_FORMAT),
            self::IMAGE                       => $this->image,
            self::VALUES                      => $this->values,
            self::PERMISSION                  => [
                self::EDIT_PERMISSION => $this->isAllowedToEdit,
            ],
        ];
    }
}
