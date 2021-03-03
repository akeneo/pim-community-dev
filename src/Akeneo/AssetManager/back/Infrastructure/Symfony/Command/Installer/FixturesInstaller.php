<?php

declare(strict_types=1);

namespace Akeneo\AssetManager\Infrastructure\Symfony\Command\Installer;

use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamily;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AttributeAsLabelReference;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AttributeAsMainMediaReference;
use Akeneo\AssetManager\Domain\Model\AssetFamily\RuleTemplateCollection;
use Akeneo\AssetManager\Domain\Model\Attribute\AbstractAttribute;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeAllowedExtensions;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeCode;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeDecimalsAllowed;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeIsReadOnly;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeIsRequired;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeLimit;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeMaxFileSize;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeOption\AttributeOption;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeOption\OptionCode;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeOrder;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeValuePerChannel;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeValuePerLocale;
use Akeneo\AssetManager\Domain\Model\Attribute\MediaFile\MediaType as MediaFileMediaType;
use Akeneo\AssetManager\Domain\Model\Attribute\MediaFileAttribute;
use Akeneo\AssetManager\Domain\Model\Attribute\MediaLink\MediaType as MediaLinkMediaType;
use Akeneo\AssetManager\Domain\Model\Attribute\MediaLink\Prefix;
use Akeneo\AssetManager\Domain\Model\Attribute\MediaLink\Suffix;
use Akeneo\AssetManager\Domain\Model\Attribute\MediaLinkAttribute;
use Akeneo\AssetManager\Domain\Model\Attribute\NumberAttribute;
use Akeneo\AssetManager\Domain\Model\Attribute\OptionCollectionAttribute;
use Akeneo\AssetManager\Domain\Model\Image;
use Akeneo\AssetManager\Domain\Model\LabelCollection;
use Akeneo\AssetManager\Domain\Repository\AssetFamilyRepositoryInterface;
use Akeneo\AssetManager\Domain\Repository\AssetRepositoryInterface;
use Akeneo\AssetManager\Domain\Repository\AttributeRepositoryInterface;
use Akeneo\AssetManager\Infrastructure\Filesystem\Storage;
use Akeneo\AssetManager\Infrastructure\Persistence\Sql\Asset\Hydrator\ValueHydratorInterface;
use Akeneo\AssetManager\Infrastructure\Symfony\Command\IndexAssetsCommand;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;
use Akeneo\Tool\Component\Console\CommandLauncher;
use Akeneo\Tool\Component\FileStorage\File\FileStorerInterface;
use Akeneo\Tool\Component\FileStorage\Model\FileInfoInterface;
use Doctrine\DBAL\Connection;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 */
class FixturesInstaller
{
    private const ATMOSPHERE_ASSET_FAMILY_IDENTIFIER = 'atmosphere';
    private const PACKSHOT_ASSET_FAMILY_IDENTIFIER = 'packshot';
    private const NOTICE_ASSET_FAMILY_IDENTIFIER = 'notice';
    private const VIDEO_ASSET_FAMILY_IDENTIFIER = 'video';
    private const ZOOM_ON_MATERIAL_ASSET_FAMILY_IDENTIFIER = 'zoom_on_material';
    private const USER_GUIDE_ASSET_FAMILY_IDENTIFIER = 'user_guide';

    /** @var Connection */
    private $sqlConnection;

    /** @var FileStorerInterface */
    private $storer;

    /** @var Client */
    private $assetClient;

    /** @var CommandLauncher */
    private $commandLauncher;

    /** @var AssetFamilyRepositoryInterface */
    private $assetFamilyRepository;

    /** @var AttributeRepositoryInterface */
    private $attributeRepository;

    /** @var AssetRepositoryInterface */
    private $assetRepository;

    /** @var ValueHydratorInterface */
    private $valueHydrator;

    /** @var FixturesLoader */
    private $fixturesLoader;

    public function __construct(
        Connection $sqlConnection,
        FileStorerInterface $storer,
        Client $assetClient,
        CommandLauncher $commandLauncher,
        AssetFamilyRepositoryInterface $assetFamilyRepository,
        AttributeRepositoryInterface $attributeRepository,
        AssetRepositoryInterface $assetRepository,
        ValueHydratorInterface $valueHydrator,
        FixturesLoader $fixturesLoader
    ) {
        $this->sqlConnection = $sqlConnection;
        $this->storer = $storer;
        $this->assetClient = $assetClient;
        $this->commandLauncher = $commandLauncher;
        $this->assetFamilyRepository = $assetFamilyRepository;
        $this->attributeRepository = $attributeRepository;
        $this->assetRepository = $assetRepository;
        $this->valueHydrator = $valueHydrator;
        $this->fixturesLoader = $fixturesLoader;
    }

    public function createSchema(): void
    {
        $sql = <<<SQL
SET foreign_key_checks = 0;
DROP TABLE IF EXISTS `akeneo_asset_manager_attribute`;
DROP TABLE IF EXISTS `akeneo_asset_manager_asset`;
DROP TABLE IF EXISTS `akeneo_asset_manager_asset_family_permissions`;
DROP TABLE IF EXISTS `akeneo_asset_manager_asset_family`;

CREATE TABLE `akeneo_asset_manager_asset_family` (
    `identifier` VARCHAR(255) NOT NULL,
    `labels` JSON NOT NULL,
    `image` VARCHAR(255) NULL,
    `attribute_as_label` VARCHAR(255) NULL,
    `attribute_as_main_media` VARCHAR(255) NULL,
    `rule_templates` JSON NOT NULL,
    `transformations` JSON NOT NULL,
    `naming_convention` JSON,
    PRIMARY KEY (`identifier`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE akeneo_asset_manager_asset (
    identifier VARCHAR(255) NOT NULL,
    code VARCHAR(255) NOT NULL,
    asset_family_identifier VARCHAR(255) NOT NULL,
    value_collection JSON NOT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (identifier),
    UNIQUE akeneoasset_manager_identifier_asset_ux (asset_family_identifier, code),
    CONSTRAINT akeneoasset_manager_asset_family_identifier_foreign_key FOREIGN KEY (asset_family_identifier) REFERENCES akeneo_asset_manager_asset_family (identifier)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `akeneo_asset_manager_attribute` (
    `identifier` VARCHAR(255) NOT NULL,
    `code` VARCHAR(255) NOT NULL,
    `asset_family_identifier` VARCHAR(255) NOT NULL,
    `labels` JSON NOT NULL,
    `attribute_type` VARCHAR(255) NOT NULL,
    `attribute_order` INT NOT NULL,
    `is_required` BOOLEAN NOT NULL,
    `value_per_channel` BOOLEAN NOT NULL,
    `value_per_locale` BOOLEAN NOT NULL,
    `additional_properties` JSON NOT NULL,
    `is_read_only` BOOLEAN NOT NULL DEFAULT false,
    PRIMARY KEY (`identifier`),
    UNIQUE `attribute_identifier_index` (`code`, `asset_family_identifier`),
    UNIQUE `attribute_asset_family_order_index` (`asset_family_identifier`, `attribute_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `akeneo_asset_manager_asset_family_permissions` (
    `asset_family_identifier` VARCHAR(255) NOT NULL,
    `user_group_identifier` SMALLINT(6) NOT NULL,
    `right_level` VARCHAR(255) NOT NULL,
    PRIMARY KEY (`asset_family_identifier`, `user_group_identifier`),
    CONSTRAINT permissions_asset_family_identifier_foreign_key FOREIGN KEY (`asset_family_identifier`) REFERENCES `akeneo_asset_manager_asset_family` (identifier)
      ON DELETE CASCADE,
    CONSTRAINT asset_manager_user_group_foreign_key FOREIGN KEY (`user_group_identifier`) REFERENCES `oro_access_group` (id)
      ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE `akeneo_asset_manager_asset_family`
    ADD CONSTRAINT akeneoasset_manager_attribute_as_label_foreign_key
    FOREIGN KEY (attribute_as_label)
    REFERENCES akeneo_asset_manager_attribute (identifier)
    ON DELETE SET NULL;

ALTER TABLE `akeneo_asset_manager_asset_family`
    ADD CONSTRAINT akeneoasset_manager_attribute_as_main_media_foreign_key
    FOREIGN KEY (attribute_as_main_media)
    REFERENCES akeneo_asset_manager_attribute (identifier)
    ON DELETE SET NULL;

ALTER TABLE `akeneo_asset_manager_attribute`
    ADD CONSTRAINT attribute_asset_family_identifier_foreign_key
    FOREIGN KEY (`asset_family_identifier`)
    REFERENCES `akeneo_asset_manager_asset_family` (identifier)
    ON DELETE CASCADE;

SET foreign_key_checks = 1;
SQL;
        $this->sqlConnection->exec($sql);
    }

    /**
     * In order to correctly escape values of assets, escape as follow:
     * \n => \\\\n
     * ' => \\'
     * ' => \\\'
     */
    public function loadCatalog(): void
    {
        $this->loadAtmospheres();
        $this->loadAtmosphereAssets();

        $this->loadPackshots();
        $this->loadPackshotAssets();

        $this->loadNotices();
        $this->loadNoticeAssets();

        $this->loadVideoPresentation();
        $this->loadVideoPresentationAssets();

        $this->loadZoomOnMaterial();
        $this->loadZoomOnMaterialAssets();

        $this->loadUserGuides();
        $this->loadUserGuideAssets();

        $this->indexAssets();
    }

    private function loadPackshots(): void
    {
        // Asset family
        $atmosphereAssetFamilyIdentifier = AssetFamilyIdentifier::fromString(self::PACKSHOT_ASSET_FAMILY_IDENTIFIER);
        $atmosphere = AssetFamily::create(
            $atmosphereAssetFamilyIdentifier,
            ['en_US' => 'Packshot'],
            Image::createEmpty(),
            RuleTemplateCollection::empty()
        );
        $this->assetFamilyRepository->create($atmosphere);

        // Attributes
        $tags = OptionCollectionAttribute::create(
            AttributeIdentifier::create(self::PACKSHOT_ASSET_FAMILY_IDENTIFIER, 'tags', 'fingerprint'),
            $atmosphereAssetFamilyIdentifier,
            AttributeCode::fromString('tags'),
            LabelCollection::fromArray(['en_US' => 'Tags']),
            AttributeOrder::fromInteger(2),
            AttributeIsRequired::fromBoolean(true),
            AttributeIsReadOnly::fromBoolean(false),
            AttributeValuePerChannel::fromBoolean(false),
            AttributeValuePerLocale::fromBoolean(false)
        );
        $tags->addOption(AttributeOption::create(OptionCode::fromString('furniture'), LabelCollection::fromArray(['en_US' => 'Furniture'])));
        $tags->addOption(AttributeOption::create(OptionCode::fromString('wood'), LabelCollection::fromArray(['en_US' => 'Wood'])));
        $tags->addOption(AttributeOption::create(OptionCode::fromString('brown'), LabelCollection::fromArray(['en_US' => 'Brown'])));
        $tags->addOption(AttributeOption::create(OptionCode::fromString('dimensions'), LabelCollection::fromArray(['en_US' => 'Dimensions'])));
        $tags->addOption(AttributeOption::create(OptionCode::fromString('table'), LabelCollection::fromArray(['en_US' => 'Table'])));
        $tags->addOption(AttributeOption::create(OptionCode::fromString('gray'), LabelCollection::fromArray(['en_US' => 'Gray'])));
        $tags->addOption(AttributeOption::create(OptionCode::fromString('white'), LabelCollection::fromArray(['en_US' => 'White'])));
        $tags->addOption(AttributeOption::create(OptionCode::fromString('blue'), LabelCollection::fromArray(['en_US' => 'Blue'])));
        $this->attributeRepository->create($tags);
    }

    private function loadPackshotAssets()
    {
        $this->fixturesLoader
            ->asset(self::PACKSHOT_ASSET_FAMILY_IDENTIFIER, 'absorb_packshot_1')
            ->withValues([
                'tags' => [
                    ['channel' => null, 'locale' => null, 'data' => ['furniture']]
                ],
                AssetFamily::DEFAULT_ATTRIBUTE_AS_MAIN_MEDIA_CODE => [
                    [
                        'channel' => null,
                        'locale'  => null,
                        'data' => [
                            'size'             => 5396,
                            'filePath'         => $this->uploadMedia('absorb_packshot_1.jpg')->getKey(),
                            'mimeType'         => 'image/jpeg',
                            'extension'        => 'jpeg',
                            'originalFilename' => 'absorb_packshot_1.jpg',
                            'updatedAt'        => '2019-11-22T15:16:21+0000',
                        ],
                    ],
                ],
                'label' => [
                    ['channel' => null, 'locale' => 'en_US', 'data' => 'Absorb packshot 1']
                ],
            ])
            ->load();

        $this->fixturesLoader
            ->asset(self::PACKSHOT_ASSET_FAMILY_IDENTIFIER, 'absorb_packshot_2')
            ->withValues([
                AssetFamily::DEFAULT_ATTRIBUTE_AS_MAIN_MEDIA_CODE => [
                    [
                        'channel' => null,
                        'locale'  => null,
                        'data'    => [
                            'size'             => 5396,
                            'filePath'         => $this->uploadMedia('absorb_packshot_2.jpg')->getKey(),
                            'mimeType'         => 'image/jpeg',
                            'extension'        => 'jpeg',
                            'originalFilename' => 'absorb_packshot_2.jpg',
                            'updatedAt'        => '2019-11-22T15:16:21+0000',
                        ],
                    ],
                ],
                'label' => [
                    ['channel' => null, 'locale' => 'en_US', 'data' => 'Absorb packshot 2']
                ],
            ])
            ->load();

        $this->fixturesLoader
            ->asset(self::PACKSHOT_ASSET_FAMILY_IDENTIFIER, 'absorb_packshot_3')
            ->withValues([
                AssetFamily::DEFAULT_ATTRIBUTE_AS_MAIN_MEDIA_CODE => [
                    [
                        'channel' => null,
                        'locale'  => null,
                        'data'    => [
                            'size'             => 5396,
                            'filePath'         => $this->uploadMedia('absorb_packshot_3.jpg')->getKey(),
                            'mimeType'         => 'image/jpeg',
                            'extension'        => 'jpeg',
                            'originalFilename' => 'absorb_packshot_3.jpg',
                            'updatedAt'        => '2019-11-22T15:16:21+0000',
                        ],
                    ],
                ],
                'label' => [
                    ['channel' => null, 'locale' => 'en_US', 'data' => 'Absorb packshot 3']
                ],
            ])
            ->load();

        $this->fixturesLoader
            ->asset(self::PACKSHOT_ASSET_FAMILY_IDENTIFIER, 'absorb_packshot_4')
            ->withValues([
                AssetFamily::DEFAULT_ATTRIBUTE_AS_MAIN_MEDIA_CODE => [
                    [
                        'channel' => null,
                        'locale'  => null,
                        'data'    => [
                            'size'             => 5496,
                            'filePath'         => $this->uploadMedia('absorb_packshot_4.jpg')->getKey(),
                            'mimeType'         => 'image/jpeg',
                            'extension'        => 'jpeg',
                            'originalFilename' => 'absorb_packshot_4.jpg',
                            'updatedAt'        => '2019-11-22T15:16:21+0000',
                        ],
                    ],
                ],
                'label' => [
                    ['channel' => null, 'locale' => 'en_US', 'data' => 'Absorb packshot 4']
                ],
            ])
            ->load();

        $this->fixturesLoader
            ->asset(self::PACKSHOT_ASSET_FAMILY_IDENTIFIER, 'admete_packshot_1')
            ->withValues([
                AssetFamily::DEFAULT_ATTRIBUTE_AS_MAIN_MEDIA_CODE => [
                    [
                        'channel' => null,
                        'locale'  => null,
                        'data'    => [
                            'size'             => 5596,
                            'filePath'         => $this->uploadMedia('admete_packshot_1.jpg')->getKey(),
                            'mimeType'         => 'image/jpeg',
                            'extension'        => 'jpeg',
                            'originalFilename' => 'admete_packshot_1.jpg',
                            'updatedAt'        => '2019-11-22T15:16:21+0000',
                        ],
                    ],
                ],
                'label' => [
                    ['channel' => null, 'locale' => 'en_US', 'data' => 'Admete packshot 1']
                ],
            ])
            ->load();

        $this->fixturesLoader
            ->asset(self::PACKSHOT_ASSET_FAMILY_IDENTIFIER, 'admete_packshot_2')
            ->withValues([
                AssetFamily::DEFAULT_ATTRIBUTE_AS_MAIN_MEDIA_CODE => [
                    [
                        'channel' => null,
                        'locale'  => null,
                        'data'    => [
                            'size'             => 5596,
                            'filePath'         => $this->uploadMedia('admete_packshot_2.jpg')->getKey(),
                            'mimeType'         => 'image/jpeg',
                            'extension'        => 'jpeg',
                            'originalFilename' => 'admete_packshot_2.jpg',
                            'updatedAt'        => '2019-11-22T15:16:21+0000',
                        ],
                    ],
                ],
                'label' => [
                    ['channel' => null, 'locale' => 'en_US', 'data' => 'Admete packshot 2']
                ],
            ])
            ->load();

        $this->fixturesLoader
            ->asset(self::PACKSHOT_ASSET_FAMILY_IDENTIFIER, 'admete_packshot_3')
            ->withValues([
                AssetFamily::DEFAULT_ATTRIBUTE_AS_MAIN_MEDIA_CODE => [
                    [
                        'channel' => null,
                        'locale'  => null,
                        'data'    => [
                            'size'             => 5596,
                            'filePath'         => $this->uploadMedia('admete_packshot_3.jpg')->getKey(),
                            'mimeType'         => 'image/jpeg',
                            'extension'        => 'jpeg',
                            'originalFilename' => 'admete_packshot_3.jpg',
                            'updatedAt'        => '2019-11-22T15:16:21+0000',
                        ],
                    ],
                ],
                'label' => [
                    ['channel' => null, 'locale' => 'en_US', 'data' => 'Admete packshot 3']
                ],
            ])
            ->load();

        $this->fixturesLoader
            ->asset(self::PACKSHOT_ASSET_FAMILY_IDENTIFIER, 'admete_packshot_4')
            ->withValues([
                AssetFamily::DEFAULT_ATTRIBUTE_AS_MAIN_MEDIA_CODE => [
                    [
                        'channel' => null,
                        'locale'  => null,
                        'data'    => [
                            'size'             => 5596,
                            'filePath'         => $this->uploadMedia('admete_packshot_4.jpg')->getKey(),
                            'mimeType'         => 'image/jpeg',
                            'extension'        => 'jpeg',
                            'originalFilename' => 'admete_packshot_4.jpg',
                            'updatedAt'        => '2019-11-22T15:16:21+0000',
                        ],
                    ],
                ],
                'label' => [
                    ['channel' => null, 'locale' => 'en_US', 'data' => 'Admete packshot 4']
                ],
            ])
            ->load();
    }

    private function loadAtmospheres(): void
    {
        // Asset family
//        $ruleTemplate = [
//            'product_selections' => [
//                [
//                    'field' => 'sku',
//                    'operator'  => '=',
//                    'value'     => '{{product_sku}}'
//                ]
//            ],
//            'assign_assets_to'    => [
//                [
//                    'mode'  => 'set',
//                    'attribute' => '{{linked_attribute}}'
//                ]
//            ]
//        ];
        $atmosphereAssetFamilyIdentifier = AssetFamilyIdentifier::fromString(self::ATMOSPHERE_ASSET_FAMILY_IDENTIFIER);
        $atmosphere = AssetFamily::create(
            $atmosphereAssetFamilyIdentifier,
            ['en_US' => 'Athmospheres'],
            Image::createEmpty(),
            RuleTemplateCollection::empty()
        );
        $this->assetFamilyRepository->create($atmosphere);

        // Attributes
        $order = 2;
        $linkAtmosphere = MediaLinkAttribute::create(
            AttributeIdentifier::create(self::ATMOSPHERE_ASSET_FAMILY_IDENTIFIER, 'link_atmosphere', 'fingerprint'),
            $atmosphereAssetFamilyIdentifier,
            AttributeCode::fromString('link_atmosphere'),
            LabelCollection::fromArray(['en_US' => 'Link atmosphere']),
            AttributeOrder::fromInteger($order),
            AttributeIsRequired::fromBoolean(true),
            AttributeIsReadOnly::fromBoolean(false),
            AttributeValuePerChannel::fromBoolean(false),
            AttributeValuePerLocale::fromBoolean(false),
            Prefix::createEmpty(),
            Suffix::createEmpty(),
            MediaLinkMediaType::fromString(MediaLinkMediaType::IMAGE)
        );
        $order++;

        $tags = OptionCollectionAttribute::create(
            AttributeIdentifier::create(self::ATMOSPHERE_ASSET_FAMILY_IDENTIFIER, 'tags', 'fingerprint'),
            $atmosphereAssetFamilyIdentifier,
            AttributeCode::fromString('tags'),
            LabelCollection::fromArray(['en_US' => 'Tags']),
            AttributeOrder::fromInteger($order),
            AttributeIsRequired::fromBoolean(true),
            AttributeIsReadOnly::fromBoolean(false),
            AttributeValuePerChannel::fromBoolean(false),
            AttributeValuePerLocale::fromBoolean(false)
        );
        $tags->addOption(AttributeOption::create(OptionCode::fromString('diningroom'), LabelCollection::fromArray(['en_US' => 'Dining Room'])));
        $tags->addOption(AttributeOption::create(OptionCode::fromString('furniture'), LabelCollection::fromArray(['en_US' => 'Furniture'])));
        $tags->addOption(AttributeOption::create(OptionCode::fromString('brown'), LabelCollection::fromArray(['en_US' => 'Brown'])));
        $tags->addOption(AttributeOption::create(OptionCode::fromString('wood'), LabelCollection::fromArray(['en_US' => 'Wood'])));

        $this->attributeRepository->create($tags);
        $this->attributeRepository->create($linkAtmosphere);

        $attributeAsLabel = $this->defaultAttributeAsLabel($atmosphereAssetFamilyIdentifier);
        $updatedAtmosphere = AssetFamily::createWithAttributes(
            $atmosphereAssetFamilyIdentifier,
            ['en_US' => 'Atmosphere'],
            Image::createEmpty(),
            AttributeAsLabelReference::fromAttributeIdentifier($attributeAsLabel->getIdentifier()),
            AttributeAsMainMediaReference::fromAttributeIdentifier($linkAtmosphere->getIdentifier()),
            RuleTemplateCollection::empty()
        );

        $this->assetFamilyRepository->update($updatedAtmosphere);
    }

    private function loadAtmosphereAssets()
    {
        $this->fixturesLoader
            ->asset(self::ATMOSPHERE_ASSET_FAMILY_IDENTIFIER, 'absorb_atmosphere_1')
            ->withValues([
                'tags' => [
                    ['channel' => null, 'locale' => null, 'data' => ['diningroom', 'wood', 'brown']]
                ],
                'link_atmosphere' => [
                    ['channel' => null, 'locale' => null, 'data' => 'https://image.noelshack.com/fichiers/2019/47/3/1574262641-db0b2ad8949e903860d840bb8f0e83524dbf2cae-sdbtay005zdb-uk-tayma-3-door-sideboard-acacia-and-brass-sale-buy-lb02.jpg']
                ],
                'label' => [
                    ['channel' => null, 'locale' => 'en_US', 'data' => 'Absorb atmosphere 1']
                ],
            ])
            ->load();
        $this->fixturesLoader
            ->asset(self::ATMOSPHERE_ASSET_FAMILY_IDENTIFIER, 'absorb_atmosphere_2')
            ->withValues([
                'tags' => [
                    ['channel' => null, 'locale' => null, 'data' => ['diningroom', 'wood']]
                ],
                'link_atmosphere' => [
                    ['channel' => null, 'locale' => null, 'data' => 'https://image.noelshack.com/fichiers/2019/47/3/1574262649-26c3f534b3fc1a9c974b4bbda790f5c13df80e0f-sdbtay005zdb-uk-tayma-3-door-sideboard-acacia-and-brass-sale-buy-lb03.jpg']
                ],
                'label' => [
                    ['channel' => null, 'locale' => 'en_US', 'data' => 'Absorb atmosphere 2']
                ],
            ])
            ->load();

        $this->fixturesLoader
            ->asset(self::ATMOSPHERE_ASSET_FAMILY_IDENTIFIER, 'admete_atmosphere_1')
            ->withValues([
                'tags' => [
                    ['channel' => null, 'locale' => null, 'data' => ['diningroom']]
                ],
                'link_atmosphere' => [
                    ['channel' => null, 'locale' => null, 'data' => 'https://image.noelshack.com/fichiers/2019/47/3/1574268551-eceb1a8fbb526c7cca49b09a027c9d5476693c13-tblboo013gry-uk-boone-extra-large-dining-table-grey-lb03.jpg']
                ],
                'label' => [
                    ['channel' => null, 'locale' => 'en_US', 'data' => 'Admete Atmosphere 1']
                ],
            ])
            ->load();
        $this->fixturesLoader
            ->asset(self::ATMOSPHERE_ASSET_FAMILY_IDENTIFIER, 'admete_atmosphere_2')
            ->withValues([
                'tags' => [
                    ['channel' => null, 'locale' => null, 'data' => ['diningroom', 'wood', 'furniture']]
                ],
                'link_atmosphere' => [
                    ['channel' => null, 'locale' => null, 'data' => 'https://image.noelshack.com/fichiers/2019/47/3/1574268555-f03ba52452a83e38faef867773b42270064a6bd9-tblboo013gry-uk-boone-extra-large-dining-table-grey-lb02.jpg']
                ],
                'label' => [
                    ['channel' => null, 'locale' => 'en_US', 'data' => 'Admete Atmosphere 2']
                ],
            ])
            ->load();
    }

    private function loadNotices(): void
    {
        // Asset family
        $assetFamilyIdentifier = AssetFamilyIdentifier::fromString(self::NOTICE_ASSET_FAMILY_IDENTIFIER);
        $notice = AssetFamily::create(
            $assetFamilyIdentifier,
            ['en_US' => 'Notices'],
            Image::createEmpty(),
            RuleTemplateCollection::empty()
        );
        $this->assetFamilyRepository->create($notice);

        // Attributes
        $order = 2;
        $linkPDF = MediaLinkAttribute::create(
            AttributeIdentifier::create(self::NOTICE_ASSET_FAMILY_IDENTIFIER, 'link_pdf', 'fingerprint'),
            $assetFamilyIdentifier,
            AttributeCode::fromString('link_pdf'),
            LabelCollection::fromArray(['en_US' => 'PDF Link']),
            AttributeOrder::fromInteger($order),
            AttributeIsRequired::fromBoolean(true),
            AttributeIsReadOnly::fromBoolean(false),
            AttributeValuePerChannel::fromBoolean(false),
            AttributeValuePerLocale::fromBoolean(false),
            Prefix::createEmpty(),
            Suffix::createEmpty(),
            MediaLinkMediaType::fromString(MediaLinkMediaType::PDF)
        );
        $order++;

        $yearOfPublication = NumberAttribute::create(
            AttributeIdentifier::create(self::NOTICE_ASSET_FAMILY_IDENTIFIER, 'year_publication', 'fingerprint'),
            $assetFamilyIdentifier,
            AttributeCode::fromString('year_publication'),
            LabelCollection::fromArray(['en_US' => 'Year of publication']),
            AttributeOrder::fromInteger($order),
            AttributeIsRequired::fromBoolean(true),
            AttributeIsReadOnly::fromBoolean(false),
            AttributeValuePerChannel::fromBoolean(false),
            AttributeValuePerLocale::fromBoolean(false),
            AttributeDecimalsAllowed::fromBoolean(false),
            AttributeLimit::fromString('2000'),
            AttributeLimit::fromString('2020')
        );

        $this->attributeRepository->create($linkPDF);
        $this->attributeRepository->create($yearOfPublication);

        $attributeAsLabel = $this->defaultAttributeAsLabel($assetFamilyIdentifier);

        $updatedAtmosphere = AssetFamily::createWithAttributes(
            $assetFamilyIdentifier,
            ['en_US' => 'Notice'],
            Image::createEmpty(),
            AttributeAsLabelReference::fromAttributeIdentifier($attributeAsLabel->getIdentifier()),
            AttributeAsMainMediaReference::fromAttributeIdentifier($linkPDF->getIdentifier()),
            RuleTemplateCollection::empty()
        );

        $this->assetFamilyRepository->update($updatedAtmosphere);
    }

    private function loadNoticeAssets(): void
    {
        $this->fixturesLoader
            ->asset(self::NOTICE_ASSET_FAMILY_IDENTIFIER, 'absorb_notice_2')
            ->withValues([
                'link_pdf' => [
                    ['channel' => null, 'locale' => null, 'data' => 'https://www.ikea.com/fr/fr/manuals/stockholm-buffet__AA-2180177-1_pub.pdf']
                ],
            ])
            ->load();

        $this->fixturesLoader
            ->asset(self::NOTICE_ASSET_FAMILY_IDENTIFIER, 'absorb_notice_3')
            ->withValues([
                'label' => [
                    ['channel' => null, 'locale' => 'en_US', 'data' => 'Absorb Notice 3']
                ],
                'link_pdf' => [
                    ['channel' => null, 'locale' => null, 'data' => 'https://www.ikea.com/fr/fr/assembly_instructions/besta-structure__AA-1272068-5_pub.pdf']
                ],
            ])
            ->load();
    }

    private function loadVideoPresentation(): void
    {
//        $ruleTemplateToAdd = [
//            'product_selections' => [
//                [
//                    'field'    => 'sku',
//                    'operator' => '=',
//                    'value'    => '{{product_sku}}'
//                ]
//            ],
//            'assign_assets_to' => [
//                [
//                    'mode'      => 'add',
//                    'attribute' => '{{linked_attribute}}'
//                ]
//            ]
//        ];
//        $ruleTemplateToSet = [
//            'product_selections' => [
//                [
//                    'field'    => 'sku',
//                    'operator' => '=',
//                    'value'    => '{{product_sku}}'
//                ]
//            ],
//            'assign_assets_to' => [
//                [
//                    'mode'      => 'set',
//                    'attribute' => '{{linked_attribute}}'
//                ]
//            ]
//        ];
        $video = AssetFamily::create(
            AssetFamilyIdentifier::fromString(self::VIDEO_ASSET_FAMILY_IDENTIFIER),
            ['en_US' => 'Video'],
            Image::createEmpty(),
            RuleTemplateCollection::empty()
        );

        $this->assetFamilyRepository->create($video);
        $order = 2;

        $youtube = MediaLinkAttribute::create(
            AttributeIdentifier::create(self::VIDEO_ASSET_FAMILY_IDENTIFIER, 'youtube', 'fingerprint'),
            AssetFamilyIdentifier::fromString(self::VIDEO_ASSET_FAMILY_IDENTIFIER),
            AttributeCode::fromString('youtube'),
            LabelCollection::fromArray(['en_US' => 'Youtube']),
            AttributeOrder::fromInteger($order),
            AttributeIsRequired::fromBoolean(true),
            AttributeIsReadOnly::fromBoolean(false),
            AttributeValuePerChannel::fromBoolean(false),
            AttributeValuePerLocale::fromBoolean(false),
            Prefix::createEmpty(),
            Suffix::createEmpty(),
            MediaLinkMediaType::fromString(MediaLinkMediaType::YOUTUBE)
        );
        $this->attributeRepository->create($youtube);

        $updatedVideos = $this->assetFamilyRepository->getByIdentifier($video->getIdentifier());
        $updatedVideos->updateAttributeAsMainMediaReference(AttributeAsMainMediaReference::fromAttributeIdentifier($youtube->getIdentifier()));
        $this->assetFamilyRepository->update($updatedVideos);
    }

    private function loadVideoPresentationAssets(): void
    {
        $this->fixturesLoader
            ->asset(self::VIDEO_ASSET_FAMILY_IDENTIFIER, 'absorb_video')
            ->withValues([
                'youtube' => [
                    ['channel' => null, 'locale' => null, 'data' => 'mueojG-Id-8']
                ],
                'label' => [
                    ['channel' => null, 'locale' => 'en_US', 'data' => 'Absorb promotional video']
                ],
            ])
            ->load();
    }

    private function loadZoomOnMaterial(): void
    {
        $zoomOnMaterial = AssetFamily::create(
            AssetFamilyIdentifier::fromString(self::ZOOM_ON_MATERIAL_ASSET_FAMILY_IDENTIFIER),
            ['en_US' => 'Zoom on material'],
            Image::createEmpty(),
            RuleTemplateCollection::empty()
        );

        $this->assetFamilyRepository->create($zoomOnMaterial);
        $order = 2;

        $zoomImage = MediaFileAttribute::create(
            AttributeIdentifier::create(self::ZOOM_ON_MATERIAL_ASSET_FAMILY_IDENTIFIER, 'zoom_image', 'fingerprint'),
            AssetFamilyIdentifier::fromString(self::ZOOM_ON_MATERIAL_ASSET_FAMILY_IDENTIFIER),
            AttributeCode::fromString('zoom_image'),
            LabelCollection::fromArray(['en_US' => 'Zoom image', 'fr_FR' => 'Zoom sur image']),
            AttributeOrder::fromInteger($order),
            AttributeIsRequired::fromBoolean(true),
            AttributeIsReadOnly::fromBoolean(false),
            AttributeValuePerChannel::fromBoolean(false),
            AttributeValuePerLocale::fromBoolean(false),
            AttributeMaxFileSize::noLimit(),
            AttributeAllowedExtensions::fromList(AttributeAllowedExtensions::ALL_ALLOWED),
            MediaFileMediaType::fromString(MediaFileMediaType::IMAGE)
        );
        $this->attributeRepository->create($zoomImage);

        $updatedZoomOnMaterial = $this->assetFamilyRepository->getByIdentifier($zoomOnMaterial->getIdentifier());
        $updatedZoomOnMaterial->updateAttributeAsMainMediaReference(AttributeAsMainMediaReference::fromAttributeIdentifier($zoomImage->getIdentifier()));
        $this->assetFamilyRepository->update($updatedZoomOnMaterial);
    }

    private function loadZoomOnMaterialAssets(): void
    {
        $this->fixturesLoader
            ->asset(self::ZOOM_ON_MATERIAL_ASSET_FAMILY_IDENTIFIER, 'absorb_zoom')
            ->withValues([
                'zoom_image' => [
                    [
                        'channel' => 'ecommerce',
                        'locale'  => 'en_US',
                        'data'    => [
                            'size'             => 5596,
                            'filePath'         => $this->uploadMedia('admete_zoom_en_US.jpg')->getKey(),
                            'mimeType'         => 'image/jpeg',
                            'extension'        => 'jpeg',
                            'originalFilename' => 'admete_zoom_en_US.jpg',
                            'updatedAt'        => '2019-11-22T15:16:21+0000',
                        ],
                    ],
                    [
                        'channel' => 'ecommerce',
                        'locale'  => 'fr_FR',
                        'data'    => [
                            'size'             => 5596,
                            'filePath'         => $this->uploadMedia('admete_zoom_fr_FR.jpg')->getKey(),
                            'mimeType'         => 'image/jpeg',
                            'extension'        => 'jpeg',
                            'originalFilename' => 'admete_zoom_fr_FR.jpg',
                            'updatedAt'        => '2019-11-22T15:16:21+0000',
                        ],
                    ],
                    [
                        'channel' => 'ecommerce',
                        'locale'  => 'de_DE',
                        'data'    => [
                            'size'             => 5596,
                            'filePath'         => $this->uploadMedia('admete_zoom_de_DE.jpg')->getKey(),
                            'mimeType'         => 'image/jpeg',
                            'extension'        => 'jpeg',
                            'originalFilename' => 'admete_zoom_de_DE.jpg',
                            'updatedAt'        => '2019-11-22T15:16:21+0000',
                        ],
                    ],
                ],
                'label' => [
                    ['channel' => null, 'locale' => 'en_US', 'data' => 'Absorb Zoom on material']
                ],
            ])
            ->load();
    }

    private function loadUserGuides(): void
    {
        $loadUserGuides = AssetFamily::create(
            AssetFamilyIdentifier::fromString(self::USER_GUIDE_ASSET_FAMILY_IDENTIFIER),
            ['en_US' => 'User guide'],
            Image::createEmpty(),
            RuleTemplateCollection::empty()
        );

        $this->assetFamilyRepository->create($loadUserGuides);
        $order = 2;

        $document = MediaFileAttribute::create(
            AttributeIdentifier::create(self::USER_GUIDE_ASSET_FAMILY_IDENTIFIER, 'regulatory_document', 'fingerprint'),
            AssetFamilyIdentifier::fromString(self::USER_GUIDE_ASSET_FAMILY_IDENTIFIER),
            AttributeCode::fromString('document'),
            LabelCollection::fromArray(['en_US' => 'Document']),
            AttributeOrder::fromInteger($order),
            AttributeIsRequired::fromBoolean(true),
            AttributeIsReadOnly::fromBoolean(false),
            AttributeValuePerChannel::fromBoolean(false),
            AttributeValuePerLocale::fromBoolean(false),
            AttributeMaxFileSize::noLimit(),
            AttributeAllowedExtensions::fromList(['pdf']),
            MediaFileMediaType::fromString(MediaFileMediaType::PDF)
        );
        $this->attributeRepository->create($document);

        $updatedUserGuides = $this->assetFamilyRepository->getByIdentifier($loadUserGuides->getIdentifier());
        $updatedUserGuides->updateAttributeAsMainMediaReference(AttributeAsMainMediaReference::fromAttributeIdentifier($document->getIdentifier()));
        $this->assetFamilyRepository->update($updatedUserGuides);
    }

    private function loadUserGuideAssets(): void
    {
        $this->fixturesLoader
            ->asset(self::USER_GUIDE_ASSET_FAMILY_IDENTIFIER, '1_7_end_user_role')
            ->withValues([
                'document' => [
                    [
                        'channel' => null,
                        'locale'  => null,
                        'data'    => [
                            'size'             => 5596,
                            'filePath'         => $this->uploadMedia('user_guides/1_7_end_user_role.pdf')->getKey(),
                            'mimeType'         => 'application/pdf',
                            'extension'        => 'pdf',
                            'originalFilename' => '1_7_end_user_role.pdf',
                            'updatedAt'        => '2019-12-22T15:16:21+0000',
                        ],
                    ],
                ],
                'label' => [
                    ['channel' => null, 'locale' => 'en_US', 'data' => '1.7 End user role']
                ],
            ])
            ->load();
        $this->fixturesLoader
            ->asset(self::USER_GUIDE_ASSET_FAMILY_IDENTIFIER, '1_7_catalog_setting')
            ->withValues([
                'document' => [
                    [
                        'channel' => null,
                        'locale'  => null,
                        'data'    => [
                            'size'             => 5596,
                            'filePath'         => $this->uploadMedia('user_guides/1_7_catalog_setting.pdf')->getKey(),
                            'mimeType'         => 'application/pdf',
                            'extension'        => 'pdf',
                            'originalFilename' => '1_7_catalog_setting.pdf',
                            'updatedAt'        => '2019-12-22T15:16:21+0000',
                        ],
                    ],
                ],
                'label' => [
                    ['channel' => null, 'locale' => 'en_US', 'data' => '1.7 Catalog setting']
                ],
            ])
            ->load();
        $this->fixturesLoader
            ->asset(self::USER_GUIDE_ASSET_FAMILY_IDENTIFIER, '1_7_administrator')
            ->withValues([
                'document' => [
                    [
                        'channel' => null,
                        'locale'  => null,
                        'data'    => [
                            'size'             => 5596,
                            'filePath'         => $this->uploadMedia('user_guides/1_7_administrator.pdf')->getKey(),
                            'mimeType'         => 'application/pdf',
                            'extension'        => 'pdf',
                            'originalFilename' => '1_7_administrator.pdf',
                            'updatedAt'        => '2019-12-22T15:16:21+0000',
                        ],
                    ],
                ],
                'label' => [
                    ['channel' => null, 'locale' => 'en_US', 'data' => '1.7 Administrator']
                ],
            ])
            ->load();
        $this->fixturesLoader
            ->asset(self::USER_GUIDE_ASSET_FAMILY_IDENTIFIER, '1_6_end_user_role')
            ->withValues([
                'document' => [
                    [
                        'channel' => null,
                        'locale'  => null,
                        'data'    => [
                            'size'             => 5596,
                            'filePath'         => $this->uploadMedia('user_guides/1_6_end_user_role.pdf')->getKey(),
                            'mimeType'         => 'application/pdf',
                            'extension'        => 'pdf',
                            'originalFilename' => '1_6_end_user_role.pdf',
                            'updatedAt'        => '2019-12-22T15:16:21+0000',
                        ],
                    ],
                ],
                'label' => [
                    ['channel' => null, 'locale' => 'en_US', 'data' => '1.6 End user role']
                ],
            ])
            ->load();
        $this->fixturesLoader
            ->asset(self::USER_GUIDE_ASSET_FAMILY_IDENTIFIER, '1_6_catalog_setting')
            ->withValues([
                'document' => [
                    [
                        'channel' => null,
                        'locale'  => null,
                        'data'    => [
                            'size'             => 5596,
                            'filePath'         => $this->uploadMedia('user_guides/1_6_catalog_setting.pdf')->getKey(),
                            'mimeType'         => 'application/pdf',
                            'extension'        => 'pdf',
                            'originalFilename' => '1_6_catalog_setting.pdf',
                            'updatedAt'        => '2019-12-22T15:16:21+0000',
                        ],
                    ],
                ],
                'label' => [
                    ['channel' => null, 'locale' => 'en_US', 'data' => '1.6 Catalog setting']
                ],
            ])
            ->load();
        $this->fixturesLoader
            ->asset(self::USER_GUIDE_ASSET_FAMILY_IDENTIFIER, '1_6_administrator')
            ->withValues([
                'document' => [
                    [
                        'channel' => null,
                        'locale'  => null,
                        'data'    => [
                            'size'             => 5596,
                            'filePath'         => $this->uploadMedia('user_guides/1_6_administrator.pdf')->getKey(),
                            'mimeType'         => 'application/pdf',
                            'extension'        => 'pdf',
                            'originalFilename' => '1_6_administrator.pdf',
                            'updatedAt'        => '2019-12-22T15:16:21+0000',
                        ],
                    ],
                ],
                'label' => [
                    ['channel' => null, 'locale' => 'en_US', 'data' => '1.6 Administrator']
                ],
            ])
            ->load();
        $this->fixturesLoader
            ->asset(self::USER_GUIDE_ASSET_FAMILY_IDENTIFIER, '1_5_end_user_role')
            ->withValues([
                'document' => [
                    [
                        'channel' => null,
                        'locale'  => null,
                        'data'    => [
                            'size'             => 5596,
                            'filePath'         => $this->uploadMedia('user_guides/1_5_end_user_role.pdf')->getKey(),
                            'mimeType'         => 'application/pdf',
                            'extension'        => 'pdf',
                            'originalFilename' => '1_5_end_user_role.pdf',
                            'updatedAt'        => '2019-12-22T15:16:21+0000',
                        ],
                    ],
                ],
                'label' => [
                    ['channel' => null, 'locale' => 'en_US', 'data' => '1.5 End user role']
                ],
            ])
            ->load();
        $this->fixturesLoader
            ->asset(self::USER_GUIDE_ASSET_FAMILY_IDENTIFIER, '1_5_catalog_setting')
            ->withValues([
                'document' => [
                    [
                        'channel' => null,
                        'locale'  => null,
                        'data'    => [
                            'size'             => 5596,
                            'filePath'         => $this->uploadMedia('user_guides/1_5_catalog_setting.pdf')->getKey(),
                            'mimeType'         => 'application/pdf',
                            'extension'        => 'pdf',
                            'originalFilename' => '1_5_catalog_setting.pdf',
                            'updatedAt'        => '2019-12-22T15:16:21+0000',
                        ],
                    ],
                ],
                'label' => [
                    ['channel' => null, 'locale' => 'en_US', 'data' => '1.5 Catalog setting']
                ],
            ])
            ->load();
        $this->fixturesLoader
            ->asset(self::USER_GUIDE_ASSET_FAMILY_IDENTIFIER, '1_5_administrator')
            ->withValues([
                'document' => [
                    [
                        'channel' => null,
                        'locale'  => null,
                        'data'    => [
                            'size'             => 5596,
                            'filePath'         => $this->uploadMedia('user_guides/1_5_administrator.pdf')->getKey(),
                            'mimeType'         => 'application/pdf',
                            'extension'        => 'pdf',
                            'originalFilename' => '1_5_administrator.pdf',
                            'updatedAt'        => '2019-12-22T15:16:21+0000',
                        ],
                    ],
                ],
                'label' => [
                    ['channel' => null, 'locale' => 'en_US', 'data' => '1.5 Administrator']
                ],
            ])
            ->load();
        $this->fixturesLoader
            ->asset(self::USER_GUIDE_ASSET_FAMILY_IDENTIFIER, '1_4_end_user_role')
            ->withValues([
                'document' => [
                    [
                        'channel' => null,
                        'locale'  => null,
                        'data'    => [
                            'size'             => 5596,
                            'filePath'         => $this->uploadMedia('user_guides/1_4_end_user_role.pdf')->getKey(),
                            'mimeType'         => 'application/pdf',
                            'extension'        => 'pdf',
                            'originalFilename' => '1_4_end_user_role.pdf',
                            'updatedAt'        => '2019-12-22T15:16:21+0000',
                        ],
                    ],
                ],
                'label' => [
                    ['channel' => null, 'locale' => 'en_US', 'data' => '1.4 End user role']
                ],
            ])
            ->load();
        $this->fixturesLoader
            ->asset(self::USER_GUIDE_ASSET_FAMILY_IDENTIFIER, '1_4_catalog_setting')
            ->withValues([
                'document' => [
                    [
                        'channel' => null,
                        'locale'  => null,
                        'data'    => [
                            'size'             => 5596,
                            'filePath'         => $this->uploadMedia('user_guides/1_4_catalog_setting.pdf')->getKey(),
                            'mimeType'         => 'application/pdf',
                            'extension'        => 'pdf',
                            'originalFilename' => '1_4_catalog_setting.pdf',
                            'updatedAt'        => '2019-12-22T15:16:21+0000',
                        ],
                    ],
                ],
                'label' => [
                    ['channel' => null, 'locale' => 'en_US', 'data' => '1.4 Catalog setting']
                ],
            ])
            ->load();
        $this->fixturesLoader
            ->asset(self::USER_GUIDE_ASSET_FAMILY_IDENTIFIER, '1_4_administrator')
            ->withValues([
                'document' => [
                    [
                        'channel' => null,
                        'locale'  => null,
                        'data'    => [
                            'size'             => 5596,
                            'filePath'         => $this->uploadMedia('user_guides/1_4_administrator.pdf')->getKey(),
                            'mimeType'         => 'application/pdf',
                            'extension'        => 'pdf',
                            'originalFilename' => '1_4_administrator.pdf',
                            'updatedAt'        => '2019-12-22T15:16:21+0000',
                        ],
                    ],
                ],
                'label' => [
                    ['channel' => null, 'locale' => 'en_US', 'data' => '1.4 Administrator']
                ],
            ])
            ->load();
    }

    private function uploadMedia(string $media): FileInfoInterface
    {
        $path = sprintf('/../../Resources/fixtures/files/%s', $media);
        $rawFile = new \SplFileInfo(__DIR__ . $path);

        return $this->storer->store($rawFile, Storage::FILE_STORAGE_ALIAS);
    }

    private function indexAssets(): void
    {
        $this->commandLauncher->executeForeground(
            sprintf('%s %s', IndexAssetsCommand::INDEX_ASSETS_COMMAND_NAME, '--all')
        );
        $this->assetClient->refreshIndex();
    }

    private function defaultAttributeAsLabel(AssetFamilyIdentifier $atmosphereAssetFamilyIdentifier): AbstractAttribute
    {
        $attributes = $this->attributeRepository->findByAssetFamily($atmosphereAssetFamilyIdentifier);
        $attributeAsLabel = current(array_filter($attributes,
            function (AbstractAttribute $attribute) {
                return $attribute->getCode()->equals(AttributeCode::fromString('label'));
            }
        ));

        return $attributeAsLabel;
    }

    private function defaultAttributeAsMainMedia(AssetFamilyIdentifier $atmosphereAssetFamilyIdentifier): AbstractAttribute
    {
        $attributes = $this->attributeRepository->findByAssetFamily($atmosphereAssetFamilyIdentifier);
        $attributeAsMainMedia = current(array_filter($attributes,
            function (AbstractAttribute $attribute) {
                return $attribute->getCode()->equals(AttributeCode::fromString(AssetFamily::DEFAULT_ATTRIBUTE_AS_MAIN_MEDIA_CODE));
            }
        ));

        return $attributeAsMainMedia;
    }
}
