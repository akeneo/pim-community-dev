<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2016 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\AssetManager\Domain\Model\AssetFamily;

use Akeneo\AssetManager\Domain\Model\Image;
use Akeneo\AssetManager\Domain\Model\LabelCollection;

/**
 * @author JM Leroux <jean-marie.leroux@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class AssetFamily
{
    public const DEFAULT_ATTRIBUTE_AS_LABEL_CODE = 'label';
    public const DEFAULT_ATTRIBUTE_AS_IMAGE_CODE = 'image';

    /** @var AssetFamilyIdentifier */
    private $identifier;

    /** @var LabelCollection */
    private $labelCollection;

    /** @var Image|null */
    private $image;

    /** @var AttributeAsLabelReference */
    private $attributeAsLabel;

    /** @var AttributeAsImageReference */
    private $attributeAsImage;

    private function __construct(
        AssetFamilyIdentifier $identifier,
        LabelCollection $labelCollection,
        Image $image,
        AttributeAsLabelReference $attributeAsLabel,
        AttributeAsImageReference $attributeAsImage
    ) {
        $this->identifier = $identifier;
        $this->labelCollection = $labelCollection;
        $this->image = $image;
        $this->attributeAsLabel = $attributeAsLabel;
        $this->attributeAsImage = $attributeAsImage;
    }

    public static function create(
        AssetFamilyIdentifier $identifier,
        array $rawLabelCollection,
        Image $image
    ): self {
        $labelCollection = LabelCollection::fromArray($rawLabelCollection);

        return new self(
            $identifier,
            $labelCollection,
            $image,
            AttributeAsLabelReference::noReference(),
            AttributeAsImageReference::noReference()
        );
    }

    public static function createWithAttributes(
        AssetFamilyIdentifier $identifier,
        array $rawLabelCollection,
        Image $image,
        AttributeAsLabelReference $attributeAsLabel,
        AttributeAsImageReference $attributeAsImage
    ): self {
        $labelCollection = LabelCollection::fromArray($rawLabelCollection);

        return new self(
            $identifier,
            $labelCollection,
            $image,
            $attributeAsLabel,
            $attributeAsImage
        );
    }

    public function getIdentifier(): AssetFamilyIdentifier
    {
        return $this->identifier;
    }

    public function equals(AssetFamily $assetFamily): bool
    {
        return $this->identifier->equals($assetFamily->identifier);
    }

    public function getLabel(string $localeCode): ?string
    {
        return $this->labelCollection->getLabel($localeCode);
    }

    public function getLabelCodes(): array
    {
        return $this->labelCollection->getLocaleCodes();
    }

    public function getImage(): Image
    {
        return $this->image;
    }

    public function updateLabels(LabelCollection $labelCollection): void
    {
        $labels = $this->labelCollection->normalize();
        $updatedLabels = $labelCollection->normalize();
        $this->labelCollection = LabelCollection::fromArray(array_merge($labels, $updatedLabels));
    }

    public function updateImage(Image $image): void
    {
        $this->image = $image;
    }

    public function getAttributeAsLabelReference(): AttributeAsLabelReference
    {
        return $this->attributeAsLabel;
    }

    public function updateAttributeAsLabelReference(AttributeAsLabelReference $attributeAsLabel): void
    {
        $this->attributeAsLabel = $attributeAsLabel;
    }

    public function getAttributeAsImageReference(): AttributeAsImageReference
    {
        return $this->attributeAsImage;
    }

    public function updateAttributeAsImageReference(AttributeAsImageReference $attributeAsImage): void
    {
        $this->attributeAsImage = $attributeAsImage;
    }
}
