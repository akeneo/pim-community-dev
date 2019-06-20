<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\AssetManager\Bundle\Datagrid\Normalizer;

use Akeneo\Pim\Enrichment\AssetManager\Component\Query\AssetInformation;
use Akeneo\Pim\Enrichment\AssetManager\Component\Query\GetAssetInformationQueryInterface;
use Akeneo\Pim\Enrichment\AssetManager\Component\Value\AssetSingleLinkValueInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Asset family normalizer for the datagrid
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AssetSingleLinkValueNormalizer implements NormalizerInterface
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

        return [
            'locale' => $assetFamilyValue->getLocaleCode(),
            'scope'  => $assetFamilyValue->getScopeCode(),
            'data'   => $this->formatSimpleLink($assetFamilyValue, $context['data_locale']),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null): bool
    {
        return 'datagrid' === $format && $data instanceof AssetSingleLinkValueInterface;
    }

    private function valueIsEmpty(AssetSingleLinkValueInterface $value): bool
    {
        $assetCode = $value->getData();

        return $assetCode === null || empty($assetCode->normalize());
    }

    private function formatSimpleLink(AssetSingleLinkValueInterface $value, string $catalogLocaleCode): string
    {
        $attribute = $this->attributeRepository->findOneByIdentifier($value->getAttributeCode());
        $assetInformation = $this->getAssetInformation($attribute, $value);

        if (array_key_exists($catalogLocaleCode, $assetInformation->labels)) {
            $result = $assetInformation->labels[$catalogLocaleCode] ?? null;
        } else {
            $assetCode = $value->getData()->normalize();
            $result = sprintf('[%s]', $assetCode);
        }

        return $result;
    }

    private function getAssetInformation(
        AttributeInterface $attribute,
        AssetSingleLinkValueInterface $value
    ): AssetInformation {
        $assetFamilyIdentifier = $attribute->getReferenceDataName();
        $assetCode = $value->getData()->normalize();
        $assetInformation = $this->getAssetInformationQuery->fetch($assetFamilyIdentifier, $assetCode);

        return $assetInformation;
    }
}
