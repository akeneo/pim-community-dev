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

namespace Akeneo\AssetManager\Application\Asset\CreateAsset;

use Akeneo\AssetManager\Domain\Model\Asset\Asset;
use Akeneo\AssetManager\Domain\Model\Asset\AssetCode;
use Akeneo\AssetManager\Domain\Model\Asset\Value\ChannelReference;
use Akeneo\AssetManager\Domain\Model\Asset\Value\LocaleReference;
use Akeneo\AssetManager\Domain\Model\Asset\Value\TextData;
use Akeneo\AssetManager\Domain\Model\Asset\Value\Value;
use Akeneo\AssetManager\Domain\Model\Asset\Value\ValueCollection;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AttributeAsLabelReference;
use Akeneo\AssetManager\Domain\Model\LocaleIdentifier;
use Akeneo\AssetManager\Domain\Query\AssetFamily\FindAssetFamilyAttributeAsLabelInterface;
use Akeneo\AssetManager\Domain\Repository\AssetRepositoryInterface;

/**
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2018 Akeneo SAS (https://www.akeneo.com)
 */
class CreateAssetHandler
{
    /** @var AssetRepositoryInterface */
    private $assetRepository;

    /** @var FindAssetFamilyAttributeAsLabelInterface */
    private $findAttributeAsLabel;

    public function __construct(
        AssetRepositoryInterface $assetRepository,
        FindAssetFamilyAttributeAsLabelInterface $findAttributeAsLabel
    ) {
        $this->assetRepository = $assetRepository;
        $this->findAttributeAsLabel = $findAttributeAsLabel;
    }

    public function __invoke(CreateAssetCommand $createAssetCommand): void
    {
        $code = AssetCode::fromString($createAssetCommand->code);
        $assetFamilyIdentifier = AssetFamilyIdentifier::fromString($createAssetCommand->assetFamilyIdentifier);
        $identifier = $this->assetRepository->nextIdentifier($assetFamilyIdentifier, $code);
        $labelValues = $this->getLabelValues($createAssetCommand, $assetFamilyIdentifier);

        $asset = Asset::create(
            $identifier,
            $assetFamilyIdentifier,
            $code,
            ValueCollection::fromValues($labelValues)
        );

        $this->assetRepository->create($asset);
    }

    private function getLabelValues(CreateAssetCommand $createAssetCommand, AssetFamilyIdentifier $assetFamilyIdentifier): array
    {
        if (empty($createAssetCommand->labels)) {
            return [];
        }

        /** @var AttributeAsLabelReference $attributeAsLabelReference */
        $attributeAsLabelReference = $this->findAttributeAsLabel->find($assetFamilyIdentifier);
        if ($attributeAsLabelReference->isEmpty()) {
            return [];
        }

        $labelValues = [];
        foreach ($createAssetCommand->labels as $locale => $label) {
            if (empty($label)) {
                continue;
            }

            $labelValues[] = Value::create(
                $attributeAsLabelReference->getIdentifier(),
                ChannelReference::noReference(),
                LocaleReference::fromLocaleIdentifier(LocaleIdentifier::fromCode($locale)),
                TextData::createFromNormalize($label)
            );
        }

        return $labelValues;
    }
}
