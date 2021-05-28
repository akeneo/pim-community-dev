<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\AssetManager\Infrastructure\PublicApi\Enrich;

use Akeneo\AssetManager\Domain\Model\Asset\AssetCode;
use Akeneo\AssetManager\Domain\Model\Asset\Value\ChannelReference;
use Akeneo\AssetManager\Domain\Model\Asset\Value\LocaleReference;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Query\Attribute\ValueKey;
use Akeneo\AssetManager\Domain\Repository\AssetFamilyRepositoryInterface;
use Akeneo\AssetManager\Domain\Repository\AssetRepositoryInterface;
use Akeneo\AssetManager\Domain\Repository\AttributeRepositoryInterface;
use Akeneo\AssetManager\Infrastructure\Persistence\Sql\Asset\Hydrator\AssetItem\ImagePreviewUrlGenerator;

/**
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2019 Akeneo SAS (https://www.akeneo.com)
 */
class AssetPreviewGenerator
{
    private AssetRepositoryInterface $assetRepository;

    private AssetFamilyRepositoryInterface $assetFamilyRepository;

    private AttributeRepositoryInterface $attributeRepository;

    private ImagePreviewUrlGenerator $imagePreviewUrlGenerator;

    public function __construct(
        AssetRepositoryInterface $assetRepository,
        AssetFamilyRepositoryInterface $assetFamilyRepository,
        AttributeRepositoryInterface $attributeRepository,
        ImagePreviewUrlGenerator $imagePreviewUrlGenerator
    ) {
        $this->assetRepository = $assetRepository;
        $this->assetFamilyRepository = $assetFamilyRepository;
        $this->attributeRepository = $attributeRepository;
        $this->imagePreviewUrlGenerator = $imagePreviewUrlGenerator;
    }

    public function getImageUrl(
        string $assetCode,
        string $assetFamilyIdentifier,
        ?string $channelCode,
        ?string $localeCode,
        string $format
    ): string {
        $familyIdentifier = AssetFamilyIdentifier::fromString($assetFamilyIdentifier);
        $code = AssetCode::fromString($assetCode);
        $asset = $this->assetRepository->getByAssetFamilyAndCode($familyIdentifier, $code);
        $family = $this->assetFamilyRepository->getByIdentifier($familyIdentifier);
        $attributeAsMainMediaIdentifier = $family->getAttributeAsMainMediaReference()->getIdentifier();
        $attribute = $this->attributeRepository->getByIdentifier($attributeAsMainMediaIdentifier);

        $channelReference = $attribute->hasValuePerChannel()
            ? ChannelReference::createFromNormalized($channelCode)
            : ChannelReference::noReference();

        $localeReference = $attribute->hasValuePerLocale()
            ? LocaleReference::createFromNormalized($localeCode)
            : LocaleReference::noReference();

        $valueKey = ValueKey::create(
            $attributeAsMainMediaIdentifier,
            $channelReference,
            $localeReference
        );

        $value = $asset->findValue($valueKey);

        if (null === $value) {
            $rawData = '';
        } else {
            $data = $value->getData()->normalize();
            $rawData = is_array($data) ? $data['filePath'] : $data;
        }

        return $this->imagePreviewUrlGenerator->generate(
            base64_encode($rawData),
            $attributeAsMainMediaIdentifier->stringValue(),
            $format
        );
    }
}
