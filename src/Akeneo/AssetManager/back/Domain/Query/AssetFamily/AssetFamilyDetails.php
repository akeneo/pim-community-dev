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

namespace Akeneo\AssetManager\Domain\Query\AssetFamily;

use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AttributeAsLabelReference;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AttributeAsMainMediaReference;
use Akeneo\AssetManager\Domain\Model\AssetFamily\NamingConvention\NamingConventionInterface;
use Akeneo\AssetManager\Domain\Model\Image;
use Akeneo\AssetManager\Domain\Model\LabelCollection;
use Akeneo\AssetManager\Domain\Query\AssetFamily\Connector\ConnectorTransformationCollection;
use Akeneo\AssetManager\Domain\Query\Attribute\AttributeDetails;

/**
 * Read model representing an asset family detailled for display purpose (like a form)
 *
 * @author JM Leroux <jean-marie.leroux@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class AssetFamilyDetails
{
    public const IDENTIFIER = 'identifier';
    public const LABELS = 'labels';
    public const IMAGE = 'image';
    public const ASSET_COUNT = 'asset_count';
    public const ATTRIBUTES = 'attributes';
    public const PERMISSION = 'permission';
    public const ATTRIBUTE_AS_LABEL = 'attribute_as_label';
    public const ATTRIBUTE_AS_MAIN_MEDIA = 'attribute_as_main_media';
    public const TRANSFORMATIONS = 'transformations';
    public const NAMING_CONVENTION = 'naming_convention';
    public const PRODUCT_LINK_RULES = 'product_link_rules';

    /** @var AssetFamilyIdentifier */
    public $identifier;

    /** @var LabelCollection */
    public $labels;

    /** @var Image */
    public $image;

    /** @var int */
    public $assetCount;

    /** @var AttributeDetails[] */
    public $attributes;

    /** @var AttributeAsLabelReference */
    public $attributeAsLabel;

    /** @var AttributeAsMainMediaReference */
    public $attributeAsMainMedia;

    /** @var ConnectorTransformationCollection */
    public $transformations;

    /** @var NamingConventionInterface */
    public $namingConvention;

    /** @var array */
    public $productLinkRules;

    /** @var bool */
    public $isAllowedToEdit = true;

    private const EDIT_PERMISSION = 'edit';

    public function normalize(): array
    {
        return [
            self::IDENTIFIER   => (string) $this->identifier,
            self::LABELS       => $this->labels->normalize(),
            self::IMAGE        => $this->image->normalize(),
            self::ASSET_COUNT => $this->assetCount,
            self::ATTRIBUTES   => array_map(function (AttributeDetails $attribute) {
                return $attribute->normalize();
            }, $this->attributes),
            self::PERMISSION => [
                self::EDIT_PERMISSION => $this->isAllowedToEdit,
            ],
            self::ATTRIBUTE_AS_LABEL => ($this->attributeAsLabel->isEmpty()) ? null : $this->attributeAsLabel->normalize(),
            self::ATTRIBUTE_AS_MAIN_MEDIA => ($this->attributeAsMainMedia->isEmpty()) ? null : $this->attributeAsMainMedia->normalize(),
            self::TRANSFORMATIONS => $this->transformations->normalize(),
            self::NAMING_CONVENTION => $this->namingConvention->normalize(),
            self::PRODUCT_LINK_RULES => $this->productLinkRules,
        ];
    }
}
