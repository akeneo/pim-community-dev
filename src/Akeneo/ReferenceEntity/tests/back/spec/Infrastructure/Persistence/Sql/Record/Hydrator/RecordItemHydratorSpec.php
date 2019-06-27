<?php

declare(strict_types=1);

namespace spec\Akeneo\ReferenceEntity\Infrastructure\Persistence\Sql\Record\Hydrator;

use Akeneo\ReferenceEntity\Domain\Model\Attribute\AbstractAttribute;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\ImageAttribute;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\UrlAttribute;
use Akeneo\ReferenceEntity\Domain\Model\ChannelIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\LocaleIdentifierCollection;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Query\Attribute\FindAttributesIndexedByIdentifierInterface;
use Akeneo\ReferenceEntity\Domain\Query\Attribute\FindRequiredValueKeyCollectionForChannelAndLocalesInterface;
use Akeneo\ReferenceEntity\Domain\Query\Attribute\ValueKeyCollection;
use Akeneo\ReferenceEntity\Domain\Query\Record\RecordItem;
use Akeneo\ReferenceEntity\Domain\Query\Record\RecordQuery;
use Akeneo\ReferenceEntity\Infrastructure\Persistence\Sql\Record\Hydrator\RecordItem\ImagePreviewUrlGenerator;
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
        ValueHydratorInterface $valueHydrator,
        ImagePreviewUrlGenerator $imagePreviewUrlGenerator
    ) {
        $connection->getDatabasePlatform()->willReturn(new MySqlPlatform());
        $this->beConstructedWith(
            $connection,
            $findRequiredValueKeyCollectionForChannelAndLocales,
            $findAttributesIndexedByIdentifier,
            $valueHydrator,
            $imagePreviewUrlGenerator
        );
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(RecordItemHydratorInterface::class);
    }

    public function it_hydrates_a_record_item_with_attribute_as_image_is_image_value(
        RecordQuery $recordQuery,
        FindRequiredValueKeyCollectionForChannelAndLocalesInterface $findRequiredValueKeyCollectionForChannelAndLocales,
        FindAttributesIndexedByIdentifierInterface $findAttributesIndexedByIdentifier,
        ValueKeyCollection $valueKeyCollection,
        AbstractAttribute $labelAttribute,
        ImageAttribute $imageAttribute,
        AbstractAttribute $textilesAttribute,
        ValueHydratorInterface $valueHydrator,
        ImagePreviewUrlGenerator $imagePreviewUrlGenerator
    ) {
        $recordQuery->getFilter('reference_entity')->willReturn(
            [
                'field'    => 'reference_entity',
                'operator' => '=',
                'value'    => 'wash_instruction'
            ]
        );
        $recordQuery->getChannel()->willReturn('ecommerce');
        $recordQuery->getLocale()->willReturn('fr_FR');

        $valueKeyCollection->normalize()->willReturn(
            [
                'label-fr_FR',
                'description-fr_FR',
            ]
        );

        $findRequiredValueKeyCollectionForChannelAndLocales->find(
            ReferenceEntityIdentifier::fromString('wash_instruction'),
            ChannelIdentifier::fromCode('ecommerce'),
            LocaleIdentifierCollection::fromNormalized(['fr_FR'])
        )->willReturn($valueKeyCollection);

        $findAttributesIndexedByIdentifier
            ->find(ReferenceEntityIdentifier::fromString('wash_instruction'))
            ->willReturn(
                [
                    'label'    => $labelAttribute,
                    'image'    => $imageAttribute,
                    'textiles' => $textilesAttribute,
                ]
            );

        $labelFrValue = [
            'attribute' => 'label',
            'channel'   => null,
            'locale'    => 'fr_FR',
            'data'      => 'Lavage cotton à sec',
        ];
        $labelEnValue = [
            'attribute' => 'label',
            'channel'   => null,
            'locale'    => 'en_US',
            'data'      => 'Cotton dry wash',
        ];
        $imageValue = [
            'attribute' => 'image',
            'channel'   => null,
            'locale'    => null,
            'data'      => 'cottondry.png',
        ];
        $textilesValue = [
            'attribute' => 'textiles',
            'channel'   => null,
            'locale'    => null,
            'data'      => 'cotton,silk',
        ];

        $valueHydrator->hydrate($labelFrValue, $labelAttribute, [])->willReturn($labelFrValue);
        $valueHydrator->hydrate($labelEnValue, $labelAttribute, [])->willReturn($labelEnValue);
        $valueHydrator->hydrate($imageValue, $imageAttribute, [])->willReturn(
            [
                'attribute' => 'image',
                'channel'   => null,
                'locale'    => null,
                'data'      => 'https://mypim.com/preview_images/cottondry.png.png/500x500',
            ]
        );
        $valueHydrator->hydrate($textilesValue, $textilesAttribute, [])->willReturn($textilesValue);

        $values = [
            'label-fr_FR' => $labelFrValue,
            'label-en_US' => $labelEnValue,
            'image'       => $imageValue,
            'textiles'    => $textilesValue
        ];

        $row = [
            'identifier'                  => 'dry_cotton',
            'reference_entity_identifier' => 'wash_instruction',
            'code'                        => 'dry_cotton',
            'value_collection'            => json_encode($values),
            'attribute_as_label'          => 'label',
            'attribute_as_image'          => 'image',
        ];

        $imagePreviewUrlGenerator
            ->generate('cottondry.png', 'image', 'thumbnail')
            ->willReturn('https://mypim.com/preview_images/cottondry.png.png/500x500');

        $actualRecordItem = $this->hydrate($row, $recordQuery);

        $expectedRecordItem = new RecordItem();
        $expectedRecordItem->identifier = 'dry_cotton';
        $expectedRecordItem->referenceEntityIdentifier = 'wash_instruction';
        $expectedRecordItem->code = 'dry_cotton';
        $expectedRecordItem->labels = ['fr_FR' => 'Lavage cotton à sec', 'en_US' => 'Cotton dry wash'];
        $expectedRecordItem->image = 'https://mypim.com/preview_images/cottondry.png.png/500x500';
        $expectedRecordItem->values = [
            'label-fr_FR' => [
                'attribute' => 'label',
                'channel'   => null,
                'locale'    => 'fr_FR',
                'data'      => 'Lavage cotton à sec'
            ],
            'label-en_US' => [
                'attribute' => 'label',
                'channel'   => null,
                'locale'    => 'en_US',
                'data'      => 'Cotton dry wash'
            ],
            'image'       => [
                'attribute' => 'image',
                'channel'   => null,
                'locale'    => null,
                'data'      => 'https://mypim.com/preview_images/cottondry.png.png/500x500'
            ],
            'textiles'    => [
                'attribute' => 'textiles',
                'channel'   => null,
                'locale'    => null,
                'data'      => 'cotton,silk'
            ]
        ];
        $expectedRecordItem->completeness = [
            'complete' => 1,
            'required' => 2
        ];
        $actualRecordItem->shouldBeLike($expectedRecordItem);
    }
}
