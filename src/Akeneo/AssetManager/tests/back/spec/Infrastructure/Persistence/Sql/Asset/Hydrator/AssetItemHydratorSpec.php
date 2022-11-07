<?php

declare(strict_types=1);

namespace spec\Akeneo\AssetManager\Infrastructure\Persistence\Sql\Asset\Hydrator;

use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Model\Attribute\AbstractAttribute;
use Akeneo\AssetManager\Domain\Model\Attribute\MediaFileAttribute;
use Akeneo\AssetManager\Domain\Model\ChannelIdentifier;
use Akeneo\AssetManager\Domain\Model\LocaleIdentifierCollection;
use Akeneo\AssetManager\Domain\Query\Asset\AssetItem;
use Akeneo\AssetManager\Domain\Query\Asset\AssetQuery;
use Akeneo\AssetManager\Domain\Query\Attribute\FindAttributesIndexedByIdentifierInterface;
use Akeneo\AssetManager\Domain\Query\Attribute\FindRequiredValueKeyCollectionForChannelAndLocalesInterface;
use Akeneo\AssetManager\Domain\Query\Attribute\ValueKeyCollection;
use Akeneo\AssetManager\Infrastructure\Persistence\Sql\Asset\Hydrator\AssetItem\ImagePreviewUrlGenerator;
use Akeneo\AssetManager\Infrastructure\Persistence\Sql\Asset\Hydrator\AssetItem\ValueHydratorInterface;
use Akeneo\AssetManager\Infrastructure\Persistence\Sql\Asset\Hydrator\AssetItemHydrator;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Platforms\MySQLPlatform;
use PhpSpec\ObjectBehavior;

class AssetItemHydratorSpec extends ObjectBehavior
{
    public function let(
        Connection $connection,
        FindRequiredValueKeyCollectionForChannelAndLocalesInterface $findRequiredValueKeyCollectionForChannelAndLocales,
        FindAttributesIndexedByIdentifierInterface $findAttributesIndexedByIdentifier,
        ValueHydratorInterface $valueHydrator,
        ImagePreviewUrlGenerator $imagePreviewUrlGenerator
    ) {
        $connection->getDatabasePlatform()->willReturn(new MySQLPlatform());
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
        $this->shouldHaveType(AssetItemHydrator::class);
    }

    public function it_hydrates_a_asset_item_with_attribute_as_main_media_value(
        AssetQuery $assetQuery,
        FindRequiredValueKeyCollectionForChannelAndLocalesInterface $findRequiredValueKeyCollectionForChannelAndLocales,
        FindAttributesIndexedByIdentifierInterface $findAttributesIndexedByIdentifier,
        ValueKeyCollection $valueKeyCollection,
        AbstractAttribute $labelAttribute,
        MediaFileAttribute $mediaFileAttribute,
        AbstractAttribute $textilesAttribute,
        ValueHydratorInterface $valueHydrator,
        ImagePreviewUrlGenerator $imagePreviewUrlGenerator
    ) {
        $assetQuery->getFilter('asset_family')->willReturn(
            [
                'field'    => 'asset_family',
                'operator' => '=',
                'value'    => 'wash_instruction'
            ]
        );
        $assetQuery->getChannel()->willReturn('ecommerce');
        $assetQuery->getLocale()->willReturn('fr_FR');

        $valueKeyCollection->normalize()->willReturn(
            [
                'label-fr_FR',
                'description-fr_FR',
            ]
        );

        $findRequiredValueKeyCollectionForChannelAndLocales->find(
            AssetFamilyIdentifier::fromString('wash_instruction'),
            ChannelIdentifier::fromCode('ecommerce'),
            LocaleIdentifierCollection::fromNormalized(['fr_FR'])
        )->willReturn($valueKeyCollection);

        $findAttributesIndexedByIdentifier
            ->find(AssetFamilyIdentifier::fromString('wash_instruction'))
            ->willReturn(
                [
                    'label'    => $labelAttribute,
                    'image'    => $mediaFileAttribute,
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
            'data'      => 'cottondry',
        ];
        $textilesValue = [
            'attribute' => 'textiles',
            'channel'   => null,
            'locale'    => null,
            'data'      => 'cotton,silk',
        ];

        $valueHydrator->hydrate($labelFrValue, $labelAttribute, [])->willReturn($labelFrValue);
        $valueHydrator->hydrate($labelEnValue, $labelAttribute, [])->willReturn($labelEnValue);
        $valueHydrator->hydrate($imageValue, $mediaFileAttribute, [])->willReturn(
            [
                'attribute' => 'image',
                'channel'   => null,
                'locale'    => null,
                'data'      => [
                    'filePath' => 'cottondry',
                    'originalFilename' => 'cottondry.png',
                ],
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
            'identifier'              => 'dry_cotton',
            'asset_family_identifier' => 'wash_instruction',
            'code'                    => 'dry_cotton',
            'value_collection'        => json_encode($values),
            'attribute_as_label'      => 'label',
            'attribute_as_main_media'      => 'image',
        ];

        $actualAssetItem = $this->hydrate($row, $assetQuery);

        $expectedAssetItem = new AssetItem();
        $expectedAssetItem->identifier = 'dry_cotton';
        $expectedAssetItem->assetFamilyIdentifier = 'wash_instruction';
        $expectedAssetItem->code = 'dry_cotton';
        $expectedAssetItem->labels = ['fr_FR' => 'Lavage cotton à sec', 'en_US' => 'Cotton dry wash'];
        $expectedAssetItem->image = [
            [
                'attribute' => 'image',
                'channel'   => null,
                'locale'    => null,
                'data'      => [
                    'filePath' => 'cottondry',
                    'originalFilename' => 'cottondry.png',
                ]
            ],
        ];
        $expectedAssetItem->values = [
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
                'data'      => [
                    'filePath' => 'cottondry',
                    'originalFilename' => 'cottondry.png',
                ]
            ],
            'textiles'    => [
                'attribute' => 'textiles',
                'channel'   => null,
                'locale'    => null,
                'data'      => 'cotton,silk'
            ]
        ];
        $expectedAssetItem->completeness = [
            'complete' => 1,
            'required' => 2
        ];
        $actualAssetItem->shouldBeLike($expectedAssetItem);
    }
}
