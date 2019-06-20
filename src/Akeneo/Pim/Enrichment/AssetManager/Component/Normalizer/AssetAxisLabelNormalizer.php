<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\AssetManager\Component\Normalizer;

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Enrichment\AssetManager\Component\Normalizer;

use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Query\Asset\FindAssetDetailsInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\Enrichment\Component\Product\Normalizer\InternalApi\AxisValueLabelsNormalizer\AxisValueLabelsNormalizer;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;

/**
 * @author Julian Prud'homme <julian.prudhomme@akeneo.com>
 */
class AssetAxisLabelNormalizer implements AxisValueLabelsNormalizer
{
    /** @var AttributeRepositoryInterface */
    private $attributeRepository;

    /** @var FindAssetDetailsInterface */
    private $findAssetDetails;

    public function __construct(AttributeRepositoryInterface $attributeRepository, FindAssetDetailsInterface $findAssetDetails)
    {
        $this->attributeRepository = $attributeRepository;
        $this->findAssetDetails = $findAssetDetails;
    }

    public function normalize(ValueInterface $value, string $locale): string
    {
        $attribute = $this->attributeRepository->findOneByIdentifier($value->getAttributeCode());
        $assetDetails = $this->findAssetDetails->find(AssetFamilyIdentifier::fromString($attribute->getReferenceDataName()), $value->getData());

        return $assetDetails->labels->getLabel($locale) ?? '[' . (string) $assetDetails->code . ']';
    }

    public function supports(string $attributeType): bool
    {
        return AttributeTypes::ASSET_SINGLE_LINK === $attributeType;
    }
}
