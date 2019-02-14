<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\ReferenceEntity\Bundle\Datagrid\Normalizer;

use Akeneo\Pim\Enrichment\ReferenceEntity\Bundle\Datagrid\Normalizer\ReferenceEntityCollectionValueNormalizer;
use Akeneo\Pim\Enrichment\ReferenceEntity\Component\AttributeType\ReferenceEntityCollectionType;
use Akeneo\Pim\Enrichment\ReferenceEntity\Component\Query\GetRecordInformationQueryInterface;
use Akeneo\Pim\Enrichment\ReferenceEntity\Component\Query\RecordInformation;
use Akeneo\Pim\Enrichment\ReferenceEntity\Component\Value\ReferenceEntityCollectionValue;
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
class ReferenceEntityCollectionValueNormalizerSpec extends ObjectBehavior
{
    public function let(
        IdentifiableObjectRepositoryInterface $attributeRepository,
        GetRecordInformationQueryInterface $getRecordInformationQuery
    ) {
        $this->beConstructedWith($attributeRepository, $getRecordInformationQuery);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ReferenceEntityCollectionValueNormalizer::class);
    }

    function it_formats_a_reference_entity_collection_link_value_without_labels(
        GetRecordInformationQueryInterface $getRecordInformationQuery,
        IdentifiableObjectRepositoryInterface $attributeRepository
    ) {
        $value = ReferenceEntityCollectionValue::value(
            'designers_link',
            [RecordCode::fromString('tony_stark'), RecordCode::fromString('banner')]
        );

        $this->attributeRepositoryWillReturnReferenceEntityCollectionAttribute($attributeRepository);
        $this->recordInformationWillBe($getRecordInformationQuery, 'tony_stark', []);
        $this->recordInformationWillBe($getRecordInformationQuery, 'banner', []);

        $this->normalize($value, 'datagrid', ['data_locale' => 'en_US'])
            ->shouldReturn([
                'locale' => null,
                'scope'  => null,
                'data'   => '[tony_stark], [banner]',
            ]);
    }

    function it_normalizes_the_code_if_there_is_no_value_for_the_data_locale(
        GetRecordInformationQueryInterface $getRecordInformationQuery,
        IdentifiableObjectRepositoryInterface $attributeRepository
    ) {
        $value = ReferenceEntityCollectionValue::value(
            'designers_link',
            [RecordCode::fromString('tony_stark'), RecordCode::fromString('banner')]
        );

        $this->attributeRepositoryWillReturnReferenceEntityCollectionAttribute($attributeRepository);
        $this->recordInformationWillBe($getRecordInformationQuery, 'tony_stark', ['en_US' => 'Tony Stark']);
        $this->recordInformationWillBe($getRecordInformationQuery, 'banner', ['en_US' => 'Banner the crazy']);

        $this->normalize($value, 'datagrid', ['data_locale' => 'fr_FR'])
            ->shouldReturn([
                'locale' => null,
                'scope'  => null,
                'data'   => '[tony_stark], [banner]',
            ]);
    }

    function it_formats_a_reference_entity_collection_link_value_with_labels(
        GetRecordInformationQueryInterface $getRecordInformationQuery,
        IdentifiableObjectRepositoryInterface $attributeRepository
    ) {
        $value = ReferenceEntityCollectionValue::value(
            'designers_link',
            [RecordCode::fromString('tony_stark'), RecordCode::fromString('banner')]
        );

        $this->attributeRepositoryWillReturnReferenceEntityCollectionAttribute($attributeRepository);
        $this->recordInformationWillBe($getRecordInformationQuery, 'tony_stark', ['en_US' => 'Tony Stark']);
        $this->recordInformationWillBe($getRecordInformationQuery, 'banner', ['en_US' => 'Banner the crazy']);

        $this->normalize($value, 'datagrid', ['data_locale' => 'en_US'])
            ->shouldReturn([
                'locale' => null,
                'scope'  => null,
                'data'   => 'Tony Stark, Banner the crazy',
            ]);
    }

    function it_returns_null_if_the_value_is_empty() {
        $value = ReferenceEntityCollectionValue::value('designers_link', []);
        $this->normalize($value, 'datagrid', ['data_locale' => 'en_US'])->shouldReturn(null);
    }

    function it_supports_the_datagrid_format_for_reference_entity_value()
    {
        $referenceEntityValue = ReferenceEntityCollectionValue::value('designers_link',
            [RecordCode::fromString('tony_stark')]);
        $this->supportsNormalization($referenceEntityValue, 'datagrid')->shouldReturn(true);
        $this->supportsNormalization($referenceEntityValue, 'standard')->shouldReturn(false);
        $this->supportsNormalization(new \StdClass(), 'standard')->shouldReturn(false);
    }

    private function attributeRepositoryWillReturnReferenceEntityCollectionAttribute(
        IdentifiableObjectRepositoryInterface $attributeRepository
    ): void {
        $simpleLinkAttribute = new Attribute();
        $simpleLinkAttribute->setType(ReferenceEntityCollectionType::REFERENCE_ENTITY_COLLECTION)
            ->setBackendType(AttributeTypes::BACKEND_TYPE_REF_DATA_OPTIONS)
            ->setReferenceDataName('designer');
        $attributeRepository->findOneByIdentifier('designers_link')->willReturn($simpleLinkAttribute);
    }

    private function recordInformationWillBe(
        GetRecordInformationQueryInterface $getRecordInformationQuery,
        $code,
        $labels
    ): void {
        $stark = new RecordInformation('designer', $code, $labels);
        $getRecordInformationQuery->execute('designer', $code)
            ->willReturn($stark);
    }
}
