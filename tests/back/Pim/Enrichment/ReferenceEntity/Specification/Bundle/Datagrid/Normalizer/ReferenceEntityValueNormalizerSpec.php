<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\ReferenceEntity\Bundle\Datagrid\Normalizer;

use Akeneo\Pim\Enrichment\ReferenceEntity\Bundle\Datagrid\Normalizer\ReferenceEntityValueNormalizer;
use Akeneo\Pim\Enrichment\ReferenceEntity\Component\AttributeType\ReferenceEntityType;
use Akeneo\Pim\Enrichment\ReferenceEntity\Component\Query\GetRecordInformationQueryInterface;
use Akeneo\Pim\Enrichment\ReferenceEntity\Component\Query\RecordInformation;
use Akeneo\Pim\Enrichment\ReferenceEntity\Component\Value\ReferenceEntityValue;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Model\Attribute;
use Akeneo\ReferenceEntity\Domain\Model\Record\RecordCode;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use PhpSpec\ObjectBehavior;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ReferenceEntityValueNormalizerSpec extends ObjectBehavior
{
    public function let(
        IdentifiableObjectRepositoryInterface $attributeRepository,
        GetRecordInformationQueryInterface $getRecordInformationQuery
    ) {
        $this->beConstructedWith($attributeRepository, $getRecordInformationQuery);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ReferenceEntityValueNormalizer::class);
    }

    function it_formats_a_simple_reference_entity_link_value_without_labels(
        GetRecordInformationQueryInterface $getRecordInformationQuery,
        IdentifiableObjectRepositoryInterface $attributeRepository
    ) {
        $value = ReferenceEntityValue::value('designer_link', RecordCode::fromString('tony_stark'));

        $this->attributeRepositoryWillReturnReferenceEntityAttribute($attributeRepository);
        $this->recordInformationWillBe($getRecordInformationQuery, 'tony_stark', []);

        $this->normalize($value, 'datagrid', ['data_locale' => 'en_US'])
            ->shouldReturn([
                'locale' => null,
                'scope'  => null,
                'data'   => '[tony_stark]',
            ]);
    }

    function it_normalizes_the_code_if_there_is_no_value_for_the_data_locale(
        GetRecordInformationQueryInterface $getRecordInformationQuery,
        IdentifiableObjectRepositoryInterface $attributeRepository
    ) {
        $value = ReferenceEntityValue::value('designer_link', RecordCode::fromString('tony_stark'));

        $this->attributeRepositoryWillReturnReferenceEntityAttribute($attributeRepository);
        $this->recordInformationWillBe($getRecordInformationQuery, 'tony_stark', ['en_US' => 'Tony Stark']);

        $this->normalize($value, 'datagrid', ['data_locale' => 'fr_FR'])
            ->shouldReturn([
                'locale' => null,
                'scope'  => null,
                'data'   => '[tony_stark]',
            ]);
    }

    function it_formats_a_simple_reference_entity_link_value_with_labels(
        GetRecordInformationQueryInterface $getRecordInformationQuery,
        IdentifiableObjectRepositoryInterface $attributeRepository
    ) {
        $value = ReferenceEntityValue::value('designer_link', RecordCode::fromString('tony_stark'));

        $this->attributeRepositoryWillReturnReferenceEntityAttribute($attributeRepository);
        $this->recordInformationWillBe($getRecordInformationQuery, 'tony_stark', ['en_US' => 'Tony Stark']);

        $this->normalize($value, 'datagrid', ['data_locale' => 'en_US'])
            ->shouldReturn([
                'locale' => null,
                'scope'  => null,
                'data'   => 'Tony Stark',
            ]);
    }

    function it_returns_null_if_the_value_is_empty() {
        $value = ReferenceEntityValue::value('designer_link', null);
        $this->normalize($value, 'datagrid', ['data_locale' => 'en_US'])->shouldReturn(null);
    }

    function it_formats_a_simple_reference_entity_link_value_with_labels_for_zero_value(
        GetRecordInformationQueryInterface $getRecordInformationQuery,
        IdentifiableObjectRepositoryInterface $attributeRepository
    ) {
        $value = ReferenceEntityValue::value('designer_link', RecordCode::fromString('0'));

        $this->attributeRepositoryWillReturnReferenceEntityAttribute($attributeRepository);
        $this->recordInformationWillBe($getRecordInformationQuery, '0', ['en_US' => 'this label']);

        $this->normalize($value, 'datagrid', ['data_locale' => 'en_US'])->shouldReturn([
            'locale' => null,
            'scope'  => null,
            'data'   => 'this label',
        ]);
    }

    function it_supports_the_datagrid_format_for_reference_entity_value()
    {
        $referenceEntityValue = ReferenceEntityValue::value('designer_link', RecordCode::fromString('tony_stark'));
        $this->supportsNormalization($referenceEntityValue, 'datagrid')->shouldReturn(true);
        $this->supportsNormalization($referenceEntityValue, 'standard')->shouldReturn(false);
        $this->supportsNormalization(new \StdClass(), 'standard')->shouldReturn(false);
    }

    private function attributeRepositoryWillReturnReferenceEntityAttribute(
        IdentifiableObjectRepositoryInterface $attributeRepository
    ): void {
        $simpleLinkAttribute = new Attribute();
        $simpleLinkAttribute->setType(ReferenceEntityType::REFERENCE_ENTITY)
            ->setBackendType(AttributeTypes::BACKEND_TYPE_REF_DATA_OPTION)
            ->setReferenceDataName('designer');
        $attributeRepository->findOneByIdentifier('designer_link')->willReturn($simpleLinkAttribute);
    }

    private function recordInformationWillBe(
        GetRecordInformationQueryInterface $getRecordInformationQuery,
        $code,
        $labels
    ): void {
        $recordInformation = new RecordInformation();
        $recordInformation->referenceEntityIdentifier = 'designer';
        $recordInformation->code = $code;
        $recordInformation->labels = $labels;
        $getRecordInformationQuery->fetch('designer', $code)->willReturn($recordInformation);
    }
}
