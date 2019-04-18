<?php

declare(strict_types=1);

namespace spec\Akeneo\ReferenceEntity\Infrastructure\Persistence\Sql\Record\Hydrator\RecordItem;

use Akeneo\ReferenceEntity\Domain\Model\Attribute\AbstractAttribute;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\RecordAttribute;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\RecordCollectionAttribute;
use Akeneo\ReferenceEntity\Domain\Model\LabelCollection;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Query\Record\FindRecordLabelsByCodesInterface;
use Akeneo\ReferenceEntity\Infrastructure\Persistence\Sql\Record\Hydrator\RecordItem\ValueHydratorInterface;
use PhpSpec\ObjectBehavior;

class RecordValueHydratorSpec extends ObjectBehavior
{
    public function let(FindRecordLabelsByCodesInterface $findRecordLabelsByCodes)
    {
        $this->beConstructedWith($findRecordLabelsByCodes);
    }

    public function it_is_initializable()
    {
        $this->shouldImplement(ValueHydratorInterface::class);
    }

    public function it_supports_record_type_attributes(
        RecordAttribute $recordAttribute,
        RecordCollectionAttribute $recordCollectionAttribute,
        AbstractAttribute $otherAttribute
    ) {
        $this->supports($recordAttribute)->shouldReturn(true);
        $this->supports($recordCollectionAttribute)->shouldReturn(true);
        $this->supports($otherAttribute)->shouldReturn(false);
    }

    public function it_fills_labels_linked_record_into_a_value_context(
        RecordAttribute $recordAttribute,
        ReferenceEntityIdentifier $designerIdentifier,
        LabelCollection $dysonLabels,
        FindRecordLabelsByCodesInterface $findRecordLabelsByCodes
    ) {
        $normalizedValue = [
            'attribute' => 'brands',
            'locale' => null,
            'channel' => null,
            'data' => ['ikea_id', 'madecom_id'],
        ];

        $context = [
            'labels' => [
                'adidas_id' => ['en_US' => 'Adidas', 'fr_FR' => 'Adidas'],
                'nike_id' => ['en_US' => 'Nike', 'fr_FR' => 'Nike'],
                'ikea_id' => ['en_US' => 'Ikea', 'fr_FR' => 'Ikea'],
                'madecom_id' => ['en_US' => 'Made.com', 'fr_FR' => 'Made.com'],
            ]
        ];

        $this->hydrate($normalizedValue, $recordAttribute, $context)->shouldReturn([
            'attribute' => 'brands',
            'locale' => null,
            'channel' => null,
            'data' => ['ikea_id', 'madecom_id'],
            'context' => [
                'labels' => [
                    'ikea_id' => ['en_US' => 'Ikea', 'fr_FR' => 'Ikea'],
                    'madecom_id' => ['en_US' => 'Made.com', 'fr_FR' => 'Made.com'],
                ]
            ]
        ]);
    }
}
