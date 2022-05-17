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

namespace Akeneo\AssetManager\Domain\Query\AssetFamily\Connector;

use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Model\AssetFamily\NamingConvention\NamingConventionInterface;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeCode;
use Akeneo\AssetManager\Domain\Model\Image;
use Akeneo\AssetManager\Domain\Model\LabelCollection;

/**
 * @author    Tamara Robichet <tamara.robichet@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class ConnectorAssetFamily
{
    public function __construct(
        private AssetFamilyIdentifier $identifier,
        private LabelCollection $labelCollection,
        private Image $image,
        private array $productLinkRules,
        private ConnectorTransformationCollection $transformations,
        private NamingConventionInterface $namingConvention,
        private ?AttributeCode $attributeAsMainMediaCode,
    ) {
    }

    public function normalize(): array
    {
        $normalizedLabels = $this->labelCollection->normalize();
        $normalizedAttributeAsMainMedia = $this->attributeAsMainMediaCode !== null ? (string) $this->attributeAsMainMediaCode : null;
        $normalizedNamingConvention = $this->namingConvention->normalize();

        return [
            'code' => $this->identifier->normalize(),
            'labels' => empty($normalizedLabels) ? (object) [] : $normalizedLabels,
            'attribute_as_main_media' => $normalizedAttributeAsMainMedia,
            'image' => $this->image->isEmpty() ? null : $this->image->getKey(),
            'product_link_rules' => $this->productLinkRules,
            'transformations' => $this->transformations->normalize(),
            'naming_convention' => empty($normalizedNamingConvention) ? (object) [] : $normalizedNamingConvention,
        ];
    }

    public function getIdentifier(): AssetFamilyIdentifier
    {
        return $this->identifier;
    }
}
