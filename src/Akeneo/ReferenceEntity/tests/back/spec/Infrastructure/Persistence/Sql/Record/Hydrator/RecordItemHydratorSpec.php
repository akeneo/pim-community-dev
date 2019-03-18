<?php

declare(strict_types=1);

namespace spec\Akeneo\ReferenceEntity\Infrastructure\Persistence\Sql\Record\Hydrator;

use Akeneo\ReferenceEntity\Domain\Model\ChannelIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\LocaleIdentifierCollection;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Query\Attribute\FindRequiredValueKeyCollectionForChannelAndLocalesInterface;
use Akeneo\ReferenceEntity\Domain\Query\Attribute\ValueKeyCollection;
use Akeneo\ReferenceEntity\Domain\Query\Record\RecordItem;
use Akeneo\ReferenceEntity\Domain\Query\Record\RecordQuery;
use Akeneo\ReferenceEntity\Infrastructure\Persistence\Sql\Record\Hydrator\RecordItemHydratorInterface;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Platforms\MySqlPlatform;
use PhpSpec\ObjectBehavior;

class RecordItemHydratorSpec extends ObjectBehavior
{
    public function let(
        Connection $connection,
        FindRequiredValueKeyCollectionForChannelAndLocalesInterface $findRequiredValueKeyCollectionForChannelAndLocales
    ) {
        $connection->getDatabasePlatform()->willReturn(new MySqlPlatform());
        $this->beConstructedWith($connection, $findRequiredValueKeyCollectionForChannelAndLocales);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(RecordItemHydratorInterface::class);
    }

    public function it_hydrates_a_record_item(
        RecordQuery $recordQuery,
        FindRequiredValueKeyCollectionForChannelAndLocalesInterface $findRequiredValueKeyCollectionForChannelAndLocales,
        ValueKeyCollection $valueKeyCollection
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

        $values = [
            'label-fr_FR' => [
                'attribute' => 'label',
                'channel' => null,
                'locale' => 'fr_FR',
                'data' => 'Lavage cotton à sec',
            ],
            'label-en_US' => [
                'attribute' => 'label',
                'channel' => null,
                'locale' => 'en_US',
                'data' => 'Cotton dry wash',
            ],
            'image' => [
                'attribute' => 'image',
                'channel' => null,
                'locale' => null,
                'data' => [
                    'file' => 'cottondry.png',
                    'key' => '/tmp/cottondry.png'
                ],
            ],
            'textiles' => [
                'attribute' => 'textiles',
                'channel' => null,
                'locale' => null,
                'data' => 'cotton,silk',
            ]
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
