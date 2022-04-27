<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2022 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Query\Completeness;

use Akeneo\AssetManager\Domain\Model\Attribute\MediaFile\MediaType;
use Akeneo\AssetManager\Domain\Model\Attribute\MediaFileAttribute;
use Akeneo\AssetManager\Domain\Model\Attribute\MediaLinkAttribute;
use Akeneo\AssetManager\Infrastructure\PublicApi\Platform\SqlGetAssetAttributesWithMediaInfoInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Query\Structure\GetProductAssetAndAttributesInfoInterface;
use Akeneo\Pim\Structure\Component\Query\PublicApi\Family\RequiredAttributesMask;
use Akeneo\Pim\Structure\Component\Query\PublicApi\Family\RequiredAttributesMaskForChannelAndLocale;

class FilterImageAndImageAssetAttributes implements FilterImageAndImageAssetAttributesInterface
{
    private const VALID_ATTRIBUTE_TYPES = [MediaFileAttribute::ATTRIBUTE_TYPE, MediaLinkAttribute::ATTRIBUTE_TYPE];

    public function __construct(
        private SqlGetAssetAttributesWithMediaInfoInterface $getAssetAttributeInfo,
        private GetProductAssetAndAttributesInfoInterface   $getProductAssetAndAttributesInfo
    ) {
    }

    /**
     * @inheritDoc
     */
    public function filter(array $familyCodes, array $masks): array
    {
        $assetAndAttributeInfos = $this->getProductAssetAndAttributesInfo->forProductFamilyCodes($familyCodes);

        $assetFamilyIdentifiers = $this->extractFamilyIdentifier($assetAndAttributeInfos);
        $assetFamilyAttributes = $this->getAssetAttributeInfo->forFamilyIdentifiers($assetFamilyIdentifiers);

        $assets = $this->matchAssetWithAttributes($assetAndAttributeInfos, $assetFamilyAttributes);

        $newMasks = [];
        foreach ($masks as $mask) {
            $familyCode = $mask->getFamilyCode();
            $newChannelAndLocalMasks = [];
            // We check if a family code is present in mask and in the asset images
            foreach ($mask->masks() as $channelAndLocaleMask) {
                $newAttributeList = $this->filterImageAndImageAssetAttributes(
                    $assets,
                    $channelAndLocaleMask->mask(),
                    $familyCode
                );
                $newChannelAndLocalMasks[] = new RequiredAttributesMaskForChannelAndLocale(
                    $channelAndLocaleMask->channelCode(),
                    $channelAndLocaleMask->localeCode(),
                    $newAttributeList
                );
            }
            $newMasks[$familyCode] = new RequiredAttributesMask(
                $familyCode,
                $newChannelAndLocalMasks
            );
        }

        return $newMasks;
    }

    /**
     * Given $familyCodeInfos like :
     * [
     *  'headphones' => [
     *      [
     *          'attribute_code' => "image_resolutions",
     *          'asset_family_identifier' => "plg792assetfamily"
     *      ]
     * ]
     * @param array<string, array<int, array{attribute_code: string, asset_family_identifier:string}>> $familyCodeInfos
     * @return array<string>
     */
    private function extractFamilyIdentifier(array $familyCodeInfos): array
    {
        $assetFamilyIdentifiers = [];
        foreach ($familyCodeInfos as $infos) {
            $assetFamilyIdentifiers = array_merge($assetFamilyIdentifiers, array_map(static function (array $info) {
                return $info['asset_family_identifier'];
            }, $infos));
        }

        return array_unique($assetFamilyIdentifiers);
    }

    /**
     * Return an array of asset and product attributes ordered by family code
     *
     * @param array<string, array<int, array{attribute_code: string, asset_family_identifier:string}>> $assetAndAttributeInfos
     * @param array<int, array{identifier: string, attribute_as_main_media: string, attribute_type: string, media_type: string}> $assetFamilyAttributes
     * @return  array<string, array<int, array{attributeCode: string, assetAttributeIdentifier: string, isAssetImageType: bool}>>
     */
    private function matchAssetWithAttributes(array $assetAndAttributeInfos, array $assetFamilyAttributes): array
    {
        $result = [];
        foreach ($assetAndAttributeInfos as $familyCode => $assetAndAttributeInfo) {
            foreach ($assetAndAttributeInfo as $info) {
                foreach ($assetFamilyAttributes as $assetFamilyAttribute) {
                    // Retrieve the asset family identifier from a string like "media_assetFamily_fingerprint"
                    if ($assetFamilyAttribute['identifier'] === $info['asset_family_identifier']) {
                        $result[$familyCode][] = [
                            'attributeCode' => $info['attribute_code'],
                            'assetAttributeIdentifier' => $assetFamilyAttribute['attribute_as_main_media'],
                            'isAssetImageType' => $this->isValidAssetImageType(
                                $assetFamilyAttribute['attribute_type'],
                                $assetFamilyAttribute['media_type']
                            )
                        ];
                    }
                }
            }
        }

        return $result;
    }

    private function isValidAssetImageType(string $attributeType, string $mediaType): bool
    {
        return in_array($attributeType, self::VALID_ATTRIBUTE_TYPES) && $mediaType == MediaType::IMAGE;
    }

    /**
     * Filters out assets whose type is not "image"
     */
    private function filterImageAndImageAssetAttributes(array $assets, array $attributesList, string $familyCode): array
    {
        return array_values(array_filter($attributesList, function ($attribute) use ($assets, $familyCode) {
            $isValid = true;
            $attributeCode = $this->extractAttributeCodeFromMask($attribute);
            foreach ($assets as $assetFamilyCode => $assetAttributes) {
                if ($assetFamilyCode !== $familyCode) {
                    continue;
                }
                foreach ($assetAttributes as $assetAttribute) {
                    if ($attributeCode == $assetAttribute['attributeCode'] && !$assetAttribute['isAssetImageType']) {
                        $isValid = false;
                    }
                }
            }
            return $isValid;
        }));
    }

    /**
     * Extract attribute code from formatted string : "attributeCode-channel-locale"
     *
     * @param string $attributeMask (i.e "picture-<all_channels>-<all_locales>")
     * @return string attribute code (i.e "picture")
     */
    private function extractAttributeCodeFromMask(string $attributeMask): string
    {
        return explode(
            RequiredAttributesMaskForChannelAndLocale::ATTRIBUTE_CHANNEL_LOCALE_SEPARATOR,
            $attributeMask
        )[0];
    }
}
