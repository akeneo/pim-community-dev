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
use Akeneo\AssetManager\Infrastructure\Persistence\Sql\Asset\Hydrator\AssetItem\ImagePreviewUrlGenerator;

/**
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2019 Akeneo SAS (https://www.akeneo.com)
 */
class AssetPreviewGenerator
{
    /** @var AssetRepositoryInterface */
    private $assetRepository;

    /** @var AssetFamilyRepositoryInterface */
    private $assetFamilyRepository;

    /** @var ImagePreviewUrlGenerator */
    private $imagePreviewUrlGenerator;

    public function __construct(
        AssetRepositoryInterface $assetRepository,
        AssetFamilyRepositoryInterface $assetFamilyRepository,
        ImagePreviewUrlGenerator $imagePreviewUrlGenerator
    ) {
        $this->assetRepository = $assetRepository;
        $this->assetFamilyRepository = $assetFamilyRepository;
        $this->imagePreviewUrlGenerator = $imagePreviewUrlGenerator;
    }

    public function getImageUrl(string $assetCode, string $assetFamilyIdentifier, string $format): string
    {
        $familyIdentifier = AssetFamilyIdentifier::fromString($assetFamilyIdentifier);
        $code = AssetCode::fromString($assetCode);
        $asset = $this->assetRepository->getByAssetFamilyAndCode($familyIdentifier, $code);
        $family = $this->assetFamilyRepository->getByIdentifier($familyIdentifier);
        $attributeAsImageIdentifier = $family->getAttributeAsImageReference()->getIdentifier();

        $valueKey = ValueKey::create(
            $attributeAsImageIdentifier,
            ChannelReference::noReference(),
            LocaleReference::noReference()
        );

        $value = $asset->findValue($valueKey);
        $data = $value->getData()->normalize();

        if (is_array($data)) {
            $rawData = $data['filePath'];
        } else {
            $rawData = $data;
        }

        return $this->imagePreviewUrlGenerator->generate(
            $rawData,
            $attributeAsImageIdentifier->stringValue(),
            $format
        );
    }
}
