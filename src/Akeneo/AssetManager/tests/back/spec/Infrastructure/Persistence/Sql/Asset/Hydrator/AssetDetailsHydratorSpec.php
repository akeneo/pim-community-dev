<?php

declare(strict_types=1);

namespace spec\Akeneo\AssetManager\Infrastructure\Persistence\Sql\Asset\Hydrator;

use Akeneo\AssetManager\Domain\Model\Asset\Value\Value;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\AssetManager\Domain\Model\Attribute\MediaFileAttribute;
use Akeneo\AssetManager\Domain\Model\Attribute\TextAttribute;
use Akeneo\AssetManager\Domain\Query\Attribute\FindValueKeysByAttributeTypeInterface;
use Akeneo\AssetManager\Domain\Query\Attribute\ValueKey;
use Akeneo\AssetManager\Domain\Query\Attribute\ValueKeyCollection;
use Akeneo\AssetManager\Infrastructure\Persistence\Sql\Asset\Hydrator\AssetDetailsHydratorInterface;
use Akeneo\AssetManager\Infrastructure\Persistence\Sql\Asset\Hydrator\ValueHydratorInterface;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Platforms\MySqlPlatform;
use PhpSpec\ObjectBehavior;

class AssetDetailsHydratorSpec extends ObjectBehavior
{
    public function let(
        Connection $connection,
        FindValueKeysByAttributeTypeInterface $findValueKeysByAttributeType,
        ValueHydratorInterface $valueHydrator
    ) {
        $connection->getDatabasePlatform()->willReturn(new MySqlPlatform());
        $this->beConstructedWith($connection, $findValueKeysByAttributeType, $valueHydrator);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(AssetDetailsHydratorInterface::class);
    }

    public function it_hydrates_a_asset_details(
        FindValueKeysByAttributeTypeInterface $findValueKeysByAttributeType,
        ValueHydratorInterface $valueHydrator,
        TextAttribute $labelAttribute,
        MediaFileAttribute $mediaFileAttribute,
        Value $labelfrFR,
        Value $labelenUS
    ) {
        $findValueKeysByAttributeType->find(
            AssetFamilyIdentifier::fromString('game'),
            ['asset', 'asset_collection']
        )->willReturn([]);

        $valueKeys = ValueKeyCollection::fromValueKeys([
            ValueKey::createFromNormalized('label_game_fingerprint_en_US'),
            ValueKey::createFromNormalized('label_game_fingerprint_fr_FR'),
            ValueKey::createFromNormalized('main_image_game_fingerprint'),
        ]);
        $mediaFileAttribute->getIdentifier()->willReturn(AttributeIdentifier::fromString('main_image_game_fingerprint'));
        $indexedAttributes = [
            'label_game_fingerprint' => $labelAttribute,
            'main_image_game_fingerprint' => $mediaFileAttribute,
        ];

        $labelFrFrNormalized = [
            'attribute' => 'label_game_fingerprint',
            'channel'   => null,
            'locale'    => 'fr_FR',
            'data'      => 'MMORPG Blizzard',
        ];
        $labelenUSNormalized = [
            'attribute' => 'label_game_fingerprint',
            'channel'   => null,
            'locale'    => 'en_US',
            'data'      => 'Blizzard\'s MMORPG',
        ];

        $labelfrFR->isEmpty()->willReturn(false);
        $valueHydrator->hydrate($labelFrFrNormalized, $labelAttribute)->willReturn($labelfrFR);
        $labelfrFR->normalize()->willReturn($labelFrFrNormalized);

        $labelenUS->isEmpty()->willReturn(false);
        $valueHydrator->hydrate($labelenUSNormalized, $labelAttribute)->willReturn($labelenUS);
        $labelenUS->normalize()->willReturn($labelenUSNormalized);

        $assetDetails = $this->hydrate(
            [
                'identifier'                  => 'wow_game_A8E76F8A76E87F6A',
                'code'                        => 'world_of_warcraft',
                'asset_family_identifier'     => 'game',
                'value_collection'            => json_encode([
                    'label_game_fingerprint_fr_FR' => $labelFrFrNormalized,
                    'label_game_fingerprint_en_US' => $labelenUSNormalized,
                ]),
                'attribute_as_label'          => 'label_game_fingerprint',
                'attribute_as_main_media'     => 'main_image_game_fingerprint',
            ],
            [
                'label_game_fingerprint_fr_FR' => [
                    'data'      => null,
                    'channel'   => null,
                    'locale'    => 'fr_FR',
                    'attribute' => 'label_game_fingerprint',
                ],
                'label_game_fingerprint_en_US' => [
                    'data'      => null,
                    'channel'   => null,
                    'locale'    => 'en_US',
                    'attribute' => 'label_game_fingerprint',
                ],
            ],
            $valueKeys,
            $indexedAttributes
        );

        $assetDetails->normalize()->shouldReturn([
            'identifier'                  => 'wow_game_A8E76F8A76E87F6A',
            'asset_family_identifier' => 'game',
            'code'                        => 'world_of_warcraft',
            'labels'                      => [
                'fr_FR' => 'MMORPG Blizzard',
                'en_US' => 'Blizzard\'s MMORPG',
            ],
            'image'                       => null,
            'values'                      => [
                [
                    'data'      => 'MMORPG Blizzard',
                    'channel'   => null,
                    'locale'    => 'fr_FR',
                    'attribute' => 'label_game_fingerprint',
                ],
                [
                    'data'      => 'Blizzard\'s MMORPG',
                    'channel'   => null,
                    'locale'    => 'en_US',
                    'attribute' => 'label_game_fingerprint',
                ],
            ],
            'permission'                  => [
                'edit' => true,
            ],
        ]);
    }

    public function it_does_not_keep_unexpected_values(
        FindValueKeysByAttributeTypeInterface $findValueKeysByAttributeType,
        ValueHydratorInterface $valueHydrator,
        TextAttribute $descriptionAttribute,
        MediaFileAttribute $mediaFileAttribute,
        Value $descriptionfrFR,
        Value $image
    ) {
        $findValueKeysByAttributeType->find(
            AssetFamilyIdentifier::fromString('game'),
            ['asset', 'asset_collection']
        )->willReturn([]);

        $valueKeys = ValueKeyCollection::fromValueKeys([
            ValueKey::createFromNormalized('description_game_fingerprint-fr_FR'),
            ValueKey::createFromNormalized('description_game_fingerprint-en_US'),
        ]);
        $mediaFileAttribute->getIdentifier()->willReturn(AttributeIdentifier::fromString('image_game_fingerprint'));
        $mediaFileAttribute->getType()->willReturn(MediaFileAttribute::ATTRIBUTE_TYPE);
        $indexedAttributes = [
            'description_game_fingerprint' => $descriptionAttribute,
            'image_game_fingerprint' => $mediaFileAttribute,
        ];

        $descriptionFrFrNormalized = [
            'attribute' => 'description_game_fingerprint',
            'channel'   => null,
            'locale'    => 'fr_FR',
            'data'      => 'Le fameux MMORPG PC de Blizzard',
        ];
        $imageNormalized = [
            'attribute' => 'image_game_fingerprint',
            'channel'   => null,
            'locale'    => null,
            'data'      => ['filePath' => '', 'originalFilename' => ''],
        ];

        $descriptionfrFR->isEmpty()->willReturn(false);
        $image->isEmpty()->willReturn(true);
        $valueHydrator->hydrate($descriptionFrFrNormalized, $descriptionAttribute)->willReturn($descriptionfrFR);
        $valueHydrator->hydrate($imageNormalized, $mediaFileAttribute)->willReturn($image);
        $descriptionfrFR->normalize()->willReturn($descriptionFrFrNormalized);
        $image->normalize()->willReturn($imageNormalized);

        $rawValues = [
            'description_game_fingerprint-fr_FR' => [
                'attribute' => 'description_game_fingerprint',
                'channel'   => null,
                'locale'    => 'fr_FR',
                'data'      => 'Le fameux MMORPG PC de Blizzard',
            ],
            'unknown_attribute1-fingerprint'     => [
                'attribute' => 'description_game_fingerprint',
                'channel'   => null,
                'locale'    => 'en_US',
                'data'      => 'The famous MMORPG PC Game by Blizzard',
            ],
            'unknown_attribute2-fingerprint'     => [
                'attribute' => 'boximage_game_fingerprint',
                'channel'   => 'mobile',
                'locale'    => null,
                'data'      => [
                    'file_key'          => 'A8EF76A87E68768FA768AE76F876',
                    'original_filename' => 'box_wow.png',
                ],
            ],
        ];

        $emptyValues = [
            'description_game_fingerprint-fr_FR' => [
                'attribute' => 'description_game_fingerprint',
                'channel'   => null,
                'locale'    => 'fr_FR',
                'data'      => null,
            ],
            'description_game_fingerprint-en_US' => [
                'attribute' => 'description_game_fingerprint',
                'channel'   => null,
                'locale'    => 'en_US',
                'data'      => null,
            ],
        ];

        $expectedValues = [
            [
                'attribute' => 'description_game_fingerprint',
                'channel'   => null,
                'locale'    => 'fr_FR',
                'data'      => 'Le fameux MMORPG PC de Blizzard',
            ],
            [
                'attribute' => 'description_game_fingerprint',
                'channel'   => null,
                'locale'    => 'en_US',
                'data'      => null,
            ],
        ];

        $asset = $this->hydrate(
            [
                'identifier'                  => 'wow_game_A8E76F8A76E87F6A',
                'code'                        => 'world_of_warcraft',
                'asset_family_identifier'     => 'game',
                'value_collection'            => json_encode($rawValues),
                'attribute_as_label'          => 'another_attribute_game_fingerprint',
                'attribute_as_main_media'     => 'image_game_fingerprint',
            ],
            $emptyValues,
            $valueKeys,
            $indexedAttributes
        );

        $asset->normalize()['values']->shouldBe($expectedValues);
    }
}
