<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\ReferenceEntity\Bundle\Datagrid\Normalizer;

use Akeneo\Pim\Enrichment\ReferenceEntity\Component\Query\GetRecordInformationQueryInterface;
use Akeneo\Pim\Enrichment\ReferenceEntity\Component\Query\RecordInformation;
use Akeneo\Pim\Enrichment\ReferenceEntity\Component\Value\ReferenceEntityValueInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Reference entity normalizer for the datagrid
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ReferenceEntityValueNormalizer implements NormalizerInterface
{
    /** @var IdentifiableObjectRepositoryInterface */
    private $attributeRepository;

    /** @var GetRecordInformationQueryInterface */
    private $getRecordInformationQuery;

    public function __construct(
        IdentifiableObjectRepositoryInterface $attributeRepository,
        GetRecordInformationQueryInterface $getRecordInformationQuery
    ) {
        $this->attributeRepository = $attributeRepository;
        $this->getRecordInformationQuery = $getRecordInformationQuery;
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($referenceEntityValue, $format = null, array $context = []): ?array
    {
        if ($this->valueIsEmpty($referenceEntityValue)) {
            return null;
        }

        return [
            'locale' => $referenceEntityValue->getLocaleCode(),
            'scope'  => $referenceEntityValue->getScopeCode(),
            'data'   => $this->formatSimpleLink($referenceEntityValue, $context['data_locale']),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null): bool
    {
        return 'datagrid' === $format && $data instanceof ReferenceEntityValueInterface;
    }

    private function valueIsEmpty(ReferenceEntityValueInterface $value): bool
    {
        $recordCode = $value->getData();

        return $recordCode === null || empty($recordCode->normalize());
    }

    private function formatSimpleLink(ReferenceEntityValueInterface $value, string $catalogLocaleCode): string
    {
        $attribute = $this->attributeRepository->findOneByIdentifier($value->getAttributeCode());
        $recordInformation = $this->getRecordInformation($attribute, $value);

        if (array_key_exists($catalogLocaleCode, $recordInformation->labels)) {
            $result = $recordInformation->labels[$catalogLocaleCode] ?? null;
        } else {
            $recordCode = $value->getData()->normalize();
            $result = sprintf('[%s]', $recordCode);
        }

        return $result;
    }

    private function getRecordInformation(
        AttributeInterface $attribute,
        ReferenceEntityValueInterface $value
    ): RecordInformation {
        $referenceEntityIdentifier = $attribute->getReferenceDataName();
        $recordCode = $value->getData()->normalize();
        $recordInformation = $this->getRecordInformationQuery->fetch($referenceEntityIdentifier, $recordCode);

        return $recordInformation;
    }
}
