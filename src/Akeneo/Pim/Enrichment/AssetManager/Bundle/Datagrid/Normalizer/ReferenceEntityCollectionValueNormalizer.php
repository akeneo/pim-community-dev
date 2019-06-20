<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\AssetManager\Bundle\Datagrid\Normalizer;

use Akeneo\Pim\Enrichment\AssetManager\Component\Query\GetAssetInformationQueryInterface;
use Akeneo\Pim\Enrichment\AssetManager\Component\Query\AssetInformation;
use Akeneo\Pim\Enrichment\AssetManager\Component\Value\AssetMultipleLinkValueInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\AssetManager\Domain\Model\Asset\AssetCode;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Asset family normalizer for the datagrid
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AssetMultipleLinkValueNormalizer implements NormalizerInterface
{
    /** @var IdentifiableObjectRepositoryInterface */
    private $attributeRepository;

    /** @var GetAssetInformationQueryInterface */
    private $getAssetInformationQuery;

    public function __construct(
        IdentifiableObjectRepositoryInterface $attributeRepository,
        GetAssetInformationQueryInterface $getAssetInformationQuery
    ) {
        $this->attributeRepository = $attributeRepository;
        $this->getAssetInformationQuery = $getAssetInformationQuery;
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($assetFamilyValue, $format = null, array $context = []): ?array
    {
        if ($this->valueIsEmpty($assetFamilyValue)) {
            return null;
        }

        $arr = [
            'locale' => $assetFamilyValue->getLocaleCode(),
            'scope'  => $assetFamilyValue->getScopeCode(),
            'data'   => $this->formatMultipleLinks($assetFamilyValue, $context['data_locale']),
        ];

        return $arr;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null): bool
    {
        return 'datagrid' === $format && $data instanceof AssetMultipleLinkValueInterface;
    }

    private function valueIsEmpty(AssetMultipleLinkValueInterface $value): bool
    {
        return empty($value->getData());
    }

    private function formatMultipleLinks(AssetMultipleLinkValueInterface $value, string $catalogLocaleCode)
    {
        $attribute = $this->attributeRepository->findOneByIdentifier($value->getAttributeCode());

        $labels = array_map(
            function (AssetCode $assetCode) use ($attribute, $catalogLocaleCode) {
                return $this->formatLink($assetCode, $attribute, $catalogLocaleCode);
            },
            $value->getData()
        );

        return implode(', ', $labels);
    }

    private function formatLink(AssetCode $assetCode, AttributeInterface $attribute, string $catalogLocaleCode): string
    {
        $assetInformation = $this->getAssetInformation($attribute, $assetCode);

        if (array_key_exists($catalogLocaleCode, $assetInformation->labels)) {
            $result = $assetInformation->labels[$catalogLocaleCode] ?? null;
        } else {
            $result = sprintf('[%s]', $assetCode->normalize());
        }

        return $result;
    }

    private function getAssetInformation(AttributeInterface $attribute, AssetCode $assetCode): AssetInformation
    {
        $assetFamilyIdentifier = $attribute->getReferenceDataName();
        $assetInformation = $this->getAssetInformationQuery->fetch(
            $assetFamilyIdentifier,
            $assetCode->normalize()
        );

        return $assetInformation;
    }
}
