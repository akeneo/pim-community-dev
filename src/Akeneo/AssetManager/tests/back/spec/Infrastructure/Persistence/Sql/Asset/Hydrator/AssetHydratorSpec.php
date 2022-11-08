<?php

declare(strict_types=1);

namespace spec\Akeneo\AssetManager\Infrastructure\Persistence\Sql\Asset\Hydrator;

use Akeneo\AssetManager\Domain\Model\Asset\AssetCode;
use Akeneo\AssetManager\Domain\Model\Asset\AssetIdentifier;
use Akeneo\AssetManager\Domain\Model\Asset\Value\Value;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\AssetManager\Domain\Model\Attribute\MediaFileAttribute;
use Akeneo\AssetManager\Domain\Model\Attribute\TextAttribute;
use Akeneo\AssetManager\Domain\Query\Attribute\ValueKey;
use Akeneo\AssetManager\Domain\Query\Attribute\ValueKeyCollection;
use Akeneo\AssetManager\Infrastructure\Persistence\Sql\Asset\Hydrator\AssetHydrator;
use Akeneo\AssetManager\Infrastructure\Persistence\Sql\Asset\Hydrator\ValueHydratorInterface;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Platforms\MySqlPlatform;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class AssetHydratorSpec extends ObjectBehavior
{
    public function let(ValueHydratorInterface $valueHydrator, Connection $connection)
    {
        $connection->getDatabasePlatform()->willReturn(new MySqlPlatform());
        $this->beConstructedWith($connection, $valueHydrator);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(AssetHydrator::class);
    }

    public function it_hydrates_a_asset(
        $valueHydrator,
        TextAttribute $label,
        MediaFileAttribute $image,
        TextAttribute $gameDescription,
        MediaFileAttribute $gameBoxImage
    ) {
        $indexedAttributes = [
            'label_game_fingerprint'    => $label,
            'image_game_fingerprint'    => $image,
            'description_game_finger'   => $gameDescription,
            'boximage_game_fingerprint' => $gameBoxImage,
        ];
        $expectedValueKeys = ValueKeyCollection::fromValueKeys([
            ValueKey::createFromNormalized('label_game_fingerprint-en_US'),
            ValueKey::createFromNormalized('label_game_fingerprint-fr_FR'),
            ValueKey::createFromNormalized('label_game_fingerprint'),
            ValueKey::createFromNormalized('description_game_fingerprint-fr_FR'),
            ValueKey::createFromNormalized('description_game_fingerprint-en_US'),
            ValueKey::createFromNormalized('boximage_game_fingerprint-mobile'),
        ]);
        $asset = $this->hydrate(
            [
                'identifier'                  => 'wow_game_A8E76F8A76E87F6A',
                'code'                        => 'world_of_warcraft',
                'asset_family_identifier' => 'game',
                'value_collection'            => json_encode([]),
                'created_at' => '2020-05-14 16:05:27',
                'updated_at' => '2020-05-14 16:14:27',
            ],
            $expectedValueKeys,
            $indexedAttributes
        );

        $valueHydrator->hydrate()->shouldNotBeCalled();
        $asset->getIdentifier()->shouldBeAnInstanceOf(AssetIdentifier::class);
        $asset->getAssetFamilyIdentifier()->shouldBeAnInstanceOf(AssetFamilyIdentifier::class);
        $asset->getCode()->shouldBeAnInstanceOf(AssetCode::class);
    }

    public function it_hydrates_a_asset_with_values(
        $valueHydrator,
        TextAttribute $label,
        MediaFileAttribute $mediaFileAttribute,
        TextAttribute $gameDescription,
        MediaFileAttribute $gameBoxImage,
        AttributeIdentifier $gameDescriptionIdentifier,
        AttributeIdentifier $gameBoxImageIdentifier,
        Value $labelfrFR,
        Value $labelenUS,
        Value $image,
        Value $gameDescriptionFrFr,
        Value $gameDescriptionEnUS,
        Value $gameBoxImageMobile
    ) {
        $gameDescriptionIdentifier->normalize()->willReturn('description_game_fingerprint');
        $gameDescription->getIdentifier()->willReturn($gameDescriptionIdentifier);

        $labelfrFrValueKey = ValueKey::createFromNormalized('label_game_fingerprint-fr_FR');
        $labelFrFrNormalized = [
            'attribute' => 'label_game_fingerprint',
            'channel'   => null,
            'locale'    => 'fr_FR',
            'data'      => 'MMORPG Blizzard',
        ];
        $labelfrFR->normalize()->willReturn($labelFrFrNormalized);
        $labelfrFR->getValueKey()->willReturn($labelfrFrValueKey);
        $labelfrFR->isEmpty()->willReturn(false);

        $labelenUSValueKey = ValueKey::createFromNormalized('label_game_fingerprint-en_US');
        $labelenUSNormalized = [
            'attribute' => 'label_game_fingerprint',
            'channel'   => null,
            'locale'    => 'en_US',
            'data'      => 'Blizzard\'s MMORPG',
        ];
        $labelenUS->normalize()->willReturn($labelenUSNormalized);
        $labelenUS->getValueKey()->willReturn($labelenUSValueKey);
        $labelenUS->isEmpty()->willReturn(false);

        $imageValueKey = ValueKey::createFromNormalized('image_game_fingerprint');
        $imageNormalized = [
            'attribute' => 'image_game_fingerprint',
            'channel'   => null,
            'locale'    => null,
            'data'      => [
                'file_key'          => 'A1EF76A17E61761FA761AE76F176',
                'original_filename' => 'image.png',
            ],
        ];
        $image->normalize()->willReturn($imageNormalized);
        $image->getValueKey()->willReturn($imageValueKey);
        $image->isEmpty()->willReturn(false);

        $gameDescriptionFrFrValueKey = ValueKey::createFromNormalized('description_game_finger-fr_FR');
        $gameDescriptionFrFrNormalized = [
            'attribute' => 'description_game_fingerprint',
            'channel'   => null,
            'locale'    => 'fr_FR',
            'data'      => 'Le fameux MMORPG PC de Blizzard',
        ];
        $gameDescriptionFrFr->normalize()->willReturn($gameDescriptionFrFrNormalized);
        $gameDescriptionFrFr->getValueKey()->willReturn($gameDescriptionFrFrValueKey);
        $gameDescriptionFrFr->isEmpty()->willReturn(false);

        $gameDescriptionEnUsValueKey = ValueKey::createFromNormalized('description_game_finger-en_US');
        $gameDescriptionEnUSNormalized = [
            'attribute' => 'description_game_fingerprint',
            'channel'   => null,
            'locale'    => 'en_US',
            'data'      => 'The famous MMORPG PC Game by Blizzard',
        ];
        $gameDescriptionEnUS->normalize()->willReturn($gameDescriptionEnUSNormalized);
        $gameDescriptionEnUS->getValueKey()->willReturn($gameDescriptionEnUsValueKey);
        $gameDescriptionEnUS->isEmpty()->willReturn(false);

        $gameBoxImageIdentifier->normalize()->willReturn('boximage_game_fingerprint');
        $gameBoxImage->getIdentifier()->willReturn($gameBoxImageIdentifier);
        $gameBoxImageMobileValueKey = ValueKey::createFromNormalized('boximage_game_fingerprint-mobile');
        $gameBoxImageMobileNormalized = [
            'attribute' => 'boximage_game_fingerprint',
            'channel'   => 'mobile',
            'locale'    => null,
            'data'      => [
                'file_key'          => 'A8EF76A87E68768FA768AE76F876',
                'original_filename' => 'box_wow.png',
            ],
        ];
        $gameBoxImageMobile->normalize()->willReturn($gameBoxImageMobileNormalized);
        $gameBoxImageMobile->getValueKey()->willReturn($gameBoxImageMobileValueKey);
        $gameBoxImageMobile->isEmpty()->willReturn(false);

        $rawValues = [
            'label_game_fingerprint-fr_FR'     => $labelFrFrNormalized,
            'label_game_fingerprint-en_US'     => $labelenUSNormalized,
            'image_game_fingerprint'           => $imageNormalized,
            'description_game_finger-fr_FR'    => $gameDescriptionFrFrNormalized,
            'description_game_finger-en_US'    => $gameDescriptionEnUSNormalized,
            'boximage_game_fingerprint-mobile' => $gameBoxImageMobileNormalized,
        ];
        $expectedValueKeys = ValueKeyCollection::fromValueKeys([
            $labelfrFrValueKey,
            $labelenUSValueKey,
            $imageValueKey,
            $gameDescriptionFrFrValueKey,
            $gameDescriptionEnUsValueKey,
            $gameBoxImageMobileValueKey,
        ]);
        $indexedAttributes = [
            'label_game_fingerprint'       => $label,
            'image_game_fingerprint'       => $mediaFileAttribute,
            'description_game_fingerprint' => $gameDescription,
            'boximage_game_fingerprint'    => $gameBoxImage,
        ];

        $valueHydrator->hydrate($labelFrFrNormalized, $label)->willReturn($labelfrFR);
        $valueHydrator->hydrate($labelenUSNormalized, $label)->willReturn($labelenUS);
        $valueHydrator->hydrate($imageNormalized, $mediaFileAttribute)->willReturn($image);
        $valueHydrator->hydrate($gameDescriptionFrFrNormalized, $gameDescription)->willReturn($gameDescriptionFrFr);
        $valueHydrator->hydrate($gameDescriptionEnUSNormalized, $gameDescription)->willReturn($gameDescriptionEnUS);
        $valueHydrator->hydrate($gameBoxImageMobileNormalized, $gameBoxImage)->willReturn($gameBoxImageMobile);

        $asset = $this->hydrate(
            [
                'identifier'                  => 'wow_game_A8E76F8A76E87F6A',
                'code'                        => 'world_of_warcraft',
                'asset_family_identifier' => 'game',
                'value_collection'            => json_encode($rawValues),
                'created_at' => '2020-05-14 16:05:27',
                'updated_at' => '2020-05-14 16:14:27',
            ],
            $expectedValueKeys,
            $indexedAttributes
        );

        $asset->getValues()->normalize()->shouldReturn([
                'label_game_fingerprint-fr_FR'     => $labelFrFrNormalized,
                'label_game_fingerprint-en_US'     => $labelenUSNormalized,
                'image_game_fingerprint'           => $imageNormalized,
                'description_game_finger-fr_FR'    => $gameDescriptionFrFrNormalized,
                'description_game_finger-en_US'    => $gameDescriptionEnUSNormalized,
                'boximage_game_fingerprint-mobile' => $gameBoxImageMobileNormalized,
            ]
        );
    }

    public function it_does_not_hydrate_unexpected_values(
        $valueHydrator,
        TextAttribute $gameDescription,
        AttributeIdentifier $gameDescriptionIdentifier,
        Value $gameDescriptionFrFr
    ) {
        $descriptionGameFrFRValueKey = ValueKey::createFromNormalized('description_game_finger-fr_FR');
        $gameDescriptionFrFrNormalized = [
            'attribute' => 'description_game_fingerprint',
            'channel'   => null,
            'locale'    => 'fr_FR',
            'data'      => 'Le fameux MMORPG PC de Blizzard',
        ];
        $gameDescriptionFrFr->normalize()->willReturn($gameDescriptionFrFrNormalized);
        $gameDescriptionFrFr->getValueKey()->willReturn($descriptionGameFrFRValueKey);
        $gameDescriptionFrFr->isEmpty()->willReturn(false);

        $gameDescriptionIdentifier->normalize()->willReturn('description_game_fingerprint');
        $gameDescription->getIdentifier()->willReturn($gameDescriptionIdentifier);

        $rawValues = [
            'description_game_finger-fr_FR'  => $gameDescriptionFrFrNormalized,
            'unknown_attribute1-fingerprint' => [
                'attribute' => 'description_game_fingerprint',
                'channel'   => null,
                'locale'    => 'en_US',
                'data'      => 'The famous MMORPG PC Game by Blizzard',
            ],
            'unknown_attribute2-fingerprint' => [
                'attribute' => 'boximage_game_fingerprint',
                'channel'   => 'mobile',
                'locale'    => null,
                'data'      => [
                    'file_key'          => 'A8EF76A87E68768FA768AE76F876',
                    'original_filename' => 'box_wow.png',
                ],
            ],
        ];
        $expectedValueKeys = ValueKeyCollection::fromValueKeys([$descriptionGameFrFRValueKey]);

        $indexedAttributes = ['description_game_fingerprint' => $gameDescription];

        $valueHydrator->hydrate($gameDescriptionFrFrNormalized, $gameDescription)->willReturn($gameDescriptionFrFr);
        $asset = $this->hydrate(
            [
                'identifier'                  => 'wow_game_A8E76F8A76E87F6A',
                'code'                        => 'world_of_warcraft',
                'asset_family_identifier' => 'game',
                'labels'                      => json_encode([]),
                'value_collection'            => json_encode($rawValues),
                'created_at' => '2020-05-14 16:05:27',
                'updated_at' => '2020-05-14 16:14:27',
            ],
            $expectedValueKeys,
            $indexedAttributes
        );

        $asset->getValues()->normalize()->shouldReturn([
            'description_game_finger-fr_FR' => $gameDescriptionFrFrNormalized,
        ]);
    }

    public function it_does_not_add_empty_values_to_the_value_collection(
        $valueHydrator,
        TextAttribute $gameDescription,
        AttributeIdentifier $attributeIdentifier,
        Value $emptyValue
    ) {
        $attributeIdentifier->normalize()->willReturn('description_game_fingerprint');
        $gameDescription->getIdentifier()->willReturn($attributeIdentifier);
        $indexedAttributes = ['description_game_fingerprint' => $gameDescription];
        $expectedValueKeys = ValueKeyCollection::fromValueKeys(
            [ValueKey::createFromNormalized('description_game_finger-fr_FR')]
        );
        $emptyValue->isEmpty()->willReturn(true);
        $valueHydrator->hydrate(Argument::any(), Argument::any())->willReturn($emptyValue);
        $asset = $this->hydrate(
            [
                'identifier'                  => 'wow_game_A8E76F8A76E87F6A',
                'code'                        => 'world_of_warcraft',
                'asset_family_identifier' => 'game',
                'labels'                      => json_encode([]),
                'value_collection' => json_encode(['description_game_finger-fr_FR' => ['attribute' => 'description_game_fingerprint']]),
                'created_at' => '2020-05-14 16:05:27',
                'updated_at' => '2020-05-14 16:14:27',
            ],
            $expectedValueKeys,
            $indexedAttributes
        );

        $asset->getValues()->normalize()->shouldReturn([]);
    }
}
