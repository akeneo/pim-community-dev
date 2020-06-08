<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\AssetManager\Bundle\Datagrid\Normalizer;

use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Query\Asset\FindAssetDetailsInterface;
use Akeneo\Pim\Enrichment\AssetManager\Component\Query\GetAssetInformationQueryInterface;
use Akeneo\Pim\Enrichment\AssetManager\Component\Value\AssetCollectionValueInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Symfony\Component\Serializer\Normalizer\CacheableSupportsMethodInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Asset family normalizer for the datagrid
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AssetCollectionValueNormalizer implements NormalizerInterface, CacheableSupportsMethodInterface
{
    /** @var IdentifiableObjectRepositoryInterface */
    private $attributeRepository;

    /** @var FindAssetDetailsInterface */
    private $findAssetDetailsQuery;

    public function __construct(
        IdentifiableObjectRepositoryInterface $attributeRepository,
        FindAssetDetailsInterface $findAssetDetailsQuery
    ) {
        $this->attributeRepository = $attributeRepository;
        $this->findAssetDetailsQuery = $findAssetDetailsQuery;
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($assetFamilyValue, $format = null, array $context = []): ?array
    {
        if ($this->valueIsEmpty($assetFamilyValue) || !isset($context['data_locale'], $context['data_channel'])) {
            return null;
        }

        return [
            'locale' => $assetFamilyValue->getLocaleCode(),
            'scope'  => $assetFamilyValue->getScopeCode(),
            'data'   => $this->formatAssetCollection($assetFamilyValue, $context['data_locale'], $context['data_channel']),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null): bool
    {
        return 'datagrid' === $format && $data instanceof AssetCollectionValueInterface;
    }

    public function hasCacheableSupportsMethod(): bool
    {
        return true;
    }

    private function valueIsEmpty(AssetCollectionValueInterface $value): bool
    {
        return empty($value->getData());
    }

    /**
     * @return string[]|null
     */
    private function formatAssetCollection(
        AssetCollectionValueInterface $value,
        string $catalogLocaleCode,
        string $catalogChannelCode
    ): ?array {
        $assetCodes = $value->getData();
        $attribute = $this->attributeRepository->findOneByIdentifier($value->getAttributeCode());
        if (!$attribute instanceof AttributeInterface) {
            return null;
        }

        $assetFamilyIdentifier = AssetFamilyIdentifier::fromString($attribute->getReferenceDataName());
        $firstAssetCode = array_shift($assetCodes);

        $assetDetails = $this->findAssetDetailsQuery->find($assetFamilyIdentifier, $firstAssetCode);
        if ($assetDetails === null) {
            return null;
        }

        $image = $this->findRelatedImage($assetDetails->image, $catalogLocaleCode, $catalogChannelCode);
        if (null === $image) {
            return [
                'attribute' => $assetDetails->attributeAsMainMediaIdentifier->normalize(),
            ];
        }

        return [
            'data' => $image['data'],
            'attribute' => $assetDetails->attributeAsMainMediaIdentifier->normalize(),
        ];
    }

    /**
     * @return string[]|null
     */
    private function findRelatedImage(array $images, $catalogLocaleCode, $catalogChannelCode): ?array
    {
        $foundImage = $this->findByLocalAndScopeImage($images, $catalogLocaleCode, $catalogChannelCode);
        if ($foundImage) {
            return $foundImage;
        }

        $foundImage = $this->findByLocalImage($images, $catalogLocaleCode);
        if ($foundImage) {
            return $foundImage;
        }

        $foundImage = $this->findByScopeImage($images, $catalogChannelCode);
        if ($foundImage) {
            return $foundImage;
        }

        return $this->findUnLocalizableAndNonScopedImage($images);
    }

    /**
     * @return string[]|null
     */
    private function findByLocalAndScopeImage(
        array $images,
        string $catalogLocaleCode,
        string $catalogChannelCode
    ): ?array {
        foreach ($images as $image) {
            if ($catalogLocaleCode === $image['locale'] && $catalogChannelCode === $image['channel']) {
                return $image;
            }
        }

        return null;
    }

    /**
     * @return string[]|null
     */
    private function findByLocalImage(array $images, string $catalogLocaleCode): ?array
    {
        foreach ($images as $image) {
            if ($catalogLocaleCode === $image['locale'] && $image['channel'] === null) {
                return $image;
            }
        }

        return null;
    }

    /**
     * @return string[]|null
     */
    private function findByScopeImage(array $images, string $catalogChannelCode): ?array
    {
        foreach ($images as $image) {
            if ($catalogChannelCode === $image['channel'] && $image['locale'] === null) {
                return $image;
            }
        }

        return null;
    }

    /**
     * @return string[]|null
     */
    private function findUnLocalizableAndNonScopedImage(array $images): ?array
    {
        foreach ($images as $image) {
            if (null === $image['locale'] && null === $image['channel']) {
                return $image;
            }
        }

        return null;
    }
}
