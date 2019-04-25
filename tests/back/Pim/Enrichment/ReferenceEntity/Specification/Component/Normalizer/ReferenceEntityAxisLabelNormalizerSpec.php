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

namespace Specification\Akeneo\Pim\Enrichment\ReferenceEntity\Component\Normalizer;

use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\Enrichment\Component\Product\Normalizer\InternalApi\AxisValueLabelsNormalizer\AxisValueLabelsNormalizer;
use Akeneo\Pim\Enrichment\ReferenceEntity\Component\Normalizer\ReferenceEntityAxisLabelNormalizer;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;
use Akeneo\ReferenceEntity\Domain\Model\Image;
use Akeneo\ReferenceEntity\Domain\Model\LabelCollection;
use Akeneo\ReferenceEntity\Domain\Model\Record\RecordCode;
use Akeneo\ReferenceEntity\Domain\Model\Record\RecordIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Query\Record\FindRecordDetailsInterface;
use Akeneo\ReferenceEntity\Domain\Query\Record\RecordDetails;
use PhpSpec\ObjectBehavior;

/**
 * @author Julian Prud'homme <julian.prudhomme@akeneo.com>
 */
class ReferenceEntityAxisLabelNormalizerSpec extends ObjectBehavior
{
    function let(AttributeRepositoryInterface $attributeRepository, FindRecordDetailsInterface $findRecordDetails)
    {
        $this->beConstructedWith($attributeRepository, $findRecordDetails);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ReferenceEntityAxisLabelNormalizer::class);
        $this->shouldHaveType(AxisValueLabelsNormalizer::class);
    }

    function it_normalizes_a_reference_entity_record_label_with_translation(
        ValueInterface $value,
        AttributeInterface $attribute,
        $attributeRepository,
        $findRecordDetails
    ) {
        $value->getAttributeCode()->willReturn('designer');
        $value->getData()->willReturn(RecordCode::fromString('starck'));

        $attribute->getReferenceDataName()->willReturn('designer');
        $attributeRepository->findOneByIdentifier('designer')->willReturn($attribute);

        $recordDetails = new RecordDetails(
            RecordIdentifier::fromString('id'),
            ReferenceEntityIdentifier::fromString('designer_id'),
            RecordCode::fromString('starck'),
            LabelCollection::fromArray(['en_US' => 'Philippe Starck']),
            Image::createEmpty(),
            [],
            true
        );
        $findRecordDetails->__invoke(ReferenceEntityIdentifier::fromString('designer'), 'starck')->willReturn($recordDetails);

        $this->normalize($value, 'en_US')->shouldReturn('Philippe Starck');
    }

    function it_normalizes_a_reference_entity_record_label_without_a_translation(
        ValueInterface $value,
        AttributeInterface $attribute,
        $attributeRepository,
        $findRecordDetails
    ) {
        $value->getAttributeCode()->willReturn('designer');
        $value->getData()->willReturn(RecordCode::fromString('starck'));

        $attribute->getReferenceDataName()->willReturn('designer');
        $attributeRepository->findOneByIdentifier('designer')->willReturn($attribute);

        $recordDetails = new RecordDetails(
            RecordIdentifier::fromString('id'),
            ReferenceEntityIdentifier::fromString('designer_id'),
            RecordCode::fromString('starck'),
            LabelCollection::fromArray([]),
            Image::createEmpty(),
            [],
            true
        );
        $findRecordDetails->__invoke(ReferenceEntityIdentifier::fromString('designer'), 'starck')->willReturn($recordDetails);

        $this->normalize($value, 'en_US')->shouldReturn('[starck]');
    }
}
