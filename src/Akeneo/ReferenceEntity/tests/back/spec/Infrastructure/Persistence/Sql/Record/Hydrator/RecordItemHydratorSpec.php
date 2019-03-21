<?php

declare(strict_types=1);

namespace spec\Akeneo\ReferenceEntity\Infrastructure\Persistence\Sql\Record\Hydrator;

use Akeneo\ReferenceEntity\Domain\Model\Attribute\AbstractAttribute;
use Akeneo\ReferenceEntity\Domain\Model\ChannelIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\LocaleIdentifierCollection;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Query\Attribute\FindAttributesIndexedByIdentifierInterface;
use Akeneo\ReferenceEntity\Domain\Query\Attribute\FindRequiredValueKeyCollectionForChannelAndLocalesInterface;
use Akeneo\ReferenceEntity\Domain\Query\Attribute\ValueKeyCollection;
use Akeneo\ReferenceEntity\Domain\Query\Record\RecordItem;
use Akeneo\ReferenceEntity\Domain\Query\Record\RecordQuery;
use Akeneo\ReferenceEntity\Infrastructure\Persistence\Sql\Record\Hydrator\RecordItem\ValueHydratorInterface;
use Akeneo\ReferenceEntity\Infrastructure\Persistence\Sql\Record\Hydrator\RecordItemHydratorInterface;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Platforms\MySqlPlatform;
use PhpSpec\ObjectBehavior;

class RecordItemHydratorSpec extends ObjectBehavior
{
    public function let(
        Connection $connection,
        FindRequiredValueKeyCollectionForChannelAndLocalesInterface $findRequiredValueKeyCollectionForChannelAndLocales,
        FindAttributesIndexedByIdentifierInterface $findAttributesIndexedByIdentifier,
        ValueHydratorInterface $valueHydrator
    ) {
        $connection->getDatabasePlatform()->willReturn(new MySqlPlatform());
        $this->beConstructedWith(
            $connection,
            $findRequiredValueKeyCollectionForChannelAndLocales,
            $findAttributesIndexedByIdentifier,
            $valueHydrator
        );
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(RecordItemHydratorInterface::class);
    }

    public function it_hydrates_a_record_item(
        RecordQuery $recordQuery,
        FindRequiredValueKeyCollectionForChannelAndLocalesInterface $findRequiredValueKeyCollectionForChannelAndLocales,
        FindAttributesIndexedByIdentifierInterface $findAttributesIndexedByIdentifier,
        ValueKeyCollection $valueKeyCollection,
        AbstractAttribute $labelAttribute,
        AbstractAttribute $imageAttribute,
        AbstractAttribute $textilesAttribute,
        ValueHydratorInterface $valueHydrator
    ) {
        $recordQuery->getFilter('reference_entity')->willReturn([
            'field' => 'reference_entity',
            'operator' => '=',
            'value' => 'wash_instruction'
        ]);
        $recordQuery->getChannel()->willReturn('ecommerce');
        $recordQuery->getLocale()->willReturn('fr_FR');

        $valueKeyCollection->normalize()->willReturn([
            'label-fr_FR',
            'description-fr_FR',
        ]);

        $findRequiredValueKeyCollectionForChannelAndLocales->__invoke(
            ReferenceEntityIdentifier::fromString('wash_instruction'),
            ChannelIdentifier::fromCode('ecommerce'),
            LocaleIdentifierCollection::fromNormalized(['fr_FR'])
        )->willReturn($valueKeyCollection);

        $findAttributesIndexedByIdentifier->__invoke(ReferenceEntityIdentifier::fromString('wash_instruction'))
            ->willReturn([
                'label' => $labelAttribute,
                'image' => $imageAttribute,
                'textiles' => $textilesAttribute,
            ]);

        $labelFrValue = [
            'attribute' => 'label',
            'channel' => null,
            'locale' => 'fr_FR',
            'data' => 'Lavage cotton à sec',
        ];
        $labelEnValue = [
            'attribute' => 'label',
            'channel' => null,
            'locale' => 'en_US',
            'data' => 'Cotton dry wash',
        ];
        $imageValue = [
            'attribute' => 'image',
            'channel' => null,
            'locale' => null,
            'data' => [
                'file' => 'cottondry.png',
                'key' => '/tmp/cottondry.png'
            ],
        ];
        $textilesValue = [
            'attribute' => 'textiles',
            'channel' => null,
            'locale' => null,
            'data' => 'cotton,silk',
        ];

        $valueHydrator->hydrate($labelFrValue, $labelAttribute, [])->willReturn($labelFrValue);
        $valueHydrator->hydrate($labelEnValue, $labelAttribute, [])->willReturn($labelEnValue);
        $valueHydrator->hydrate($imageValue, $imageAttribute, [])->willReturn($imageValue);
        $valueHydrator->hydrate($textilesValue, $textilesAttribute, [])->willReturn($textilesValue);

        $values = [
            'label-fr_FR' => $labelFrValue,
            'label-en_US' => $labelEnValue,
            'image' => $imageValue,
            'textiles' => $textilesValue
        ];

        $row = [
            'identifier' => 'dry_cotton',
            'reference_entity_identifier' => 'wash_instruction',
            'code' => 'dry_cotton',
            'value_collection' => json_encode($values),
            'attribute_as_label' => 'label',
            'attribute_as_image' => 'image',
        ];

        $expectedRecordItem = new RecordItem();
        $expectedRecordItem->identifier = 'dry_cotton';
        $expectedRecordItem->referenceEntityIdentifier = 'wash_instruction';
        $expectedRecordItem->code = 'dry_cotton';
        $expectedRecordItem->labels = ['fr_FR' => 'Lavage cotton à sec', 'en_US' => 'Cotton dry wash'];
        $expectedRecordItem->image = [
            'file' => 'cottondry.png',
            'key' => '/tmp/cottondry.png'
        ];
        $expectedRecordItem->values = $values;
        $expectedRecordItem->completeness = [
            'complete' => 1,
            'required' => 2
        ];

        $this->hydrate($row, $recordQuery)->shouldBeLike($expectedRecordItem);
    }
}
