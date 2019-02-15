<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\ReferenceEntity\Bundle\Datagrid\Normalizer;

use Akeneo\Pim\Enrichment\ReferenceEntity\Component\Query\GetRecordInformationQueryInterface;
use Akeneo\Pim\Enrichment\ReferenceEntity\Component\Query\RecordInformation;
use Akeneo\Pim\Enrichment\ReferenceEntity\Component\Value\ReferenceEntityCollectionValueInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\ReferenceEntity\Domain\Model\Record\RecordCode;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Reference entity normalizer for the datagrid
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ReferenceEntityCollectionValueNormalizer implements NormalizerInterface
{
    /** @var IdentifiableObjectRepositoryInterface */
    protected $attributeRepository;

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
    public function normalize($referenceEntityValue, $format = null, array $context = [])
    {
        if ($this->valueIsEmpty($referenceEntityValue)) {
            return null;
        }

        $arr = [
            'locale' => $referenceEntityValue->getLocaleCode(),
            'scope'  => $referenceEntityValue->getScopeCode(),
            'data'   => $this->formatMultipleLinks($referenceEntityValue, $context['data_locale']),
        ];

        return $arr;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return 'datagrid' === $format && $data instanceof ReferenceEntityCollectionValueInterface;
    }

    private function valueIsEmpty(ReferenceEntityCollectionValueInterface $value): bool
    {
        return empty($value->getData());
    }

    private function formatMultipleLinks(ReferenceEntityCollectionValueInterface $value, string $catalogLocaleCode)
    {
        $attribute = $this->attributeRepository->findOneByIdentifier($value->getAttributeCode());

        $labels = array_map(
            function (RecordCode $recordCode) use ($attribute, $catalogLocaleCode) {
                return $this->formatLink($recordCode, $attribute, $catalogLocaleCode);
            },
            $value->getData()
        );

        return implode(', ', $labels);
    }

    private function formatLink(RecordCode $recordCode, AttributeInterface $attribute, string $catalogLocaleCode): string
    {
        $recordInformation = $this->getRecordInformation($attribute, $recordCode);

        if (array_key_exists($catalogLocaleCode, $recordInformation->labels)) {
            $result = $recordInformation->labels[$catalogLocaleCode] ?? null;
        } else {
            $result = sprintf('[%s]', $recordCode->normalize());
        }

        return $result;
    }

    private function getRecordInformation(AttributeInterface $attribute, RecordCode $recordCode): RecordInformation
    {
        $referenceEntityIdentifier = $attribute->getReferenceDataName();
        $recordInformation = $this->getRecordInformationQuery->fetch(
            $referenceEntityIdentifier,
            $recordCode->normalize()
        );

        return $recordInformation;
    }
}
