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

use Akeneo\AssetManager\Domain\Model\AssetFamily\NamingConvention\NamingConvention;
use Akeneo\AssetManager\Domain\Model\Image;
use Akeneo\AssetManager\Domain\Model\LabelCollection;

/**
 * @author JM Leroux <jean-marie.leroux@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class AssetFamily
{
    public const DEFAULT_ATTRIBUTE_AS_LABEL_CODE = 'label';
    public const DEFAULT_ATTRIBUTE_AS_MAIN_MEDIA_CODE = 'image';

    /** @var AssetFamilyIdentifier */
    private $identifier;

    /** @var LabelCollection */
    private $labelCollection;

    /** @var Image|null */
    private $image;

    /** @var AttributeAsLabelReference */
    private $attributeAsLabel;

    /** @var AttributeAsMainMediaReference */
    private $attributeAsMainMedia;

    /** @var RuleTemplateCollection  */
    private $ruleTemplateCollection;

    /** @var TransformationCollection */
    private $transformationCollection;

    /** @var ?NamingConvention */
    private $namingConvention;

    private function __construct(
        AssetFamilyIdentifier $identifier,
        LabelCollection $labelCollection,
        Image $image,
        AttributeAsLabelReference $attributeAsLabel,
        AttributeAsMainMediaReference $attributeAsMainMedia,
        RuleTemplateCollection $ruleTemplateCollection,
        TransformationCollection $transformationCollection,
        ?NamingConvention $namingConvention
    ) {
        $this->identifier = $identifier;
        $this->labelCollection = $labelCollection;
        $this->image = $image;
        $this->attributeAsLabel = $attributeAsLabel;
        $this->attributeAsMainMedia = $attributeAsMainMedia;
        $this->ruleTemplateCollection = $ruleTemplateCollection;
        $this->transformationCollection = $transformationCollection;
        $this->namingConvention = $namingConvention;
    }

    public static function create(
        AssetFamilyIdentifier $identifier,
        array $rawLabelCollection,
        Image $image,
        RuleTemplateCollection $ruleTemplateCollection
    ): self {
        $labelCollection = LabelCollection::fromArray($rawLabelCollection);

        return new self(
            $identifier,
            $labelCollection,
            $image,
            AttributeAsLabelReference::noReference(),
            AttributeAsMainMediaReference::noReference(),
            $ruleTemplateCollection,
            TransformationCollection::noTransformation(),
            NamingConvention::noNamingConvention()
        );
    }

    public static function createWithAttributes(
        AssetFamilyIdentifier $identifier,
        array $rawLabelCollection,
        Image $image,
        AttributeAsLabelReference $attributeAsLabel,
        AttributeAsMainMediaReference $attributeAsMainMedia,
        RuleTemplateCollection $ruleTemplateCollection
    ): self {
        $labelCollection = LabelCollection::fromArray($rawLabelCollection);

        return new self(
            $identifier,
            $labelCollection,
            $image,
            $attributeAsLabel,
            $attributeAsMainMedia,
            $ruleTemplateCollection,
            TransformationCollection::noTransformation(),
            NamingConvention::noNamingConvention()
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

    public function getAttributeAsMainMediaReference(): AttributeAsMainMediaReference
    {
        return $this->attributeAsMainMedia;
    }

    public function updateAttributeAsMainMediaReference(AttributeAsMainMediaReference $attributeAsMainMedia): void
    {
        $this->attributeAsMainMedia = $attributeAsMainMedia;
    }

    public function getRuleTemplateCollection(): RuleTemplateCollection
    {
        return $this->ruleTemplateCollection;
    }

    public function updateRuleTemplateCollection(RuleTemplateCollection $ruleTemplateCollection): void
    {
        $this->ruleTemplateCollection = $ruleTemplateCollection;
    }

    public function getTransformationCollection(): TransformationCollection
    {
        return $this->transformationCollection;
    }

    public function withTransformationCollection(TransformationCollection $transformationCollection): self
    {
        return new self(
            $this->identifier,
            $this->labelCollection,
            $this->image,
            $this->attributeAsLabel,
            $this->attributeAsMainMedia,
            $this->ruleTemplateCollection,
            $transformationCollection,
            $this->namingConvention
        );
    }

    public function withNamingConvention(NamingConvention $namingConvention): self
    {
        return new self(
            $this->identifier,
            $this->labelCollection,
            $this->image,
            $this->attributeAsLabel,
            $this->attributeAsMainMedia,
            $this->ruleTemplateCollection,
            $this->transformationCollection,
            $namingConvention
        );
    }

    public function getNamingConvention(): ?NamingConvention
    {
        return $this->namingConvention;
    }
}
