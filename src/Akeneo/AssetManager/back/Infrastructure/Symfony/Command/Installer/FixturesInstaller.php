<?php

declare(strict_types=1);

namespace Akeneo\AssetManager\Infrastructure\Symfony\Command\Installer;

use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamily;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Model\AssetFamily\RuleTemplateCollection;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeCode;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeIsRequired;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeIsRichTextEditor;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeMaxLength;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeOrder;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeRegularExpression;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeValidationRule;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeValuePerChannel;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeValuePerLocale;
use Akeneo\AssetManager\Domain\Model\Attribute\MediaLink\MediaType;
use Akeneo\AssetManager\Domain\Model\Attribute\MediaLink\Prefix;
use Akeneo\AssetManager\Domain\Model\Attribute\MediaLink\Suffix;
use Akeneo\AssetManager\Domain\Model\Attribute\MediaLinkAttribute;
use Akeneo\AssetManager\Domain\Model\Attribute\OptionAttribute;
use Akeneo\AssetManager\Domain\Model\Attribute\OptionCollectionAttribute;
use Akeneo\AssetManager\Domain\Model\Attribute\TextAttribute;
use Akeneo\AssetManager\Domain\Model\Image;
use Akeneo\AssetManager\Domain\Model\LabelCollection;
use Akeneo\AssetManager\Domain\Repository\AssetFamilyRepositoryInterface;
use Akeneo\AssetManager\Domain\Repository\AssetRepositoryInterface;
use Akeneo\AssetManager\Domain\Repository\AttributeRepositoryInterface;
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
    public const ICE_CAT_DEMO_DEV_CATALOG = 'PimEnterpriseInstallerBundle:icecat_demo_dev';
    private const CATALOG_STORAGE_ALIAS = 'catalogStorage';
    private const NUMBER_OF_FAKE_ASSET_TO_CREATE = 10000;

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
    `attribute_as_image` VARCHAR(255) NULL,
    `product_link_rules` JSON NOT NULL,
    PRIMARY KEY (`identifier`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE akeneo_asset_manager_asset (
    identifier VARCHAR(255) NOT NULL,
    code VARCHAR(255) NOT NULL,
    asset_family_identifier VARCHAR(255) NOT NULL,
    value_collection JSON NOT NULL,
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
    ADD CONSTRAINT akeneoasset_manager_attribute_as_image_foreign_key 
    FOREIGN KEY (attribute_as_image) 
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
        $this->assetClient->resetIndex();
    }

    /**
     * In order to correctly escape values of assets, escape as follow:
     * \n => \\\\n
     * ' => \\'
     * " => \\\"
     */
    public function loadCatalog(string $catalogName): void
    {
        if (self::ICE_CAT_DEMO_DEV_CATALOG !== $catalogName) {
            return;
        }

        $this->loadPackshots();
        $this->loadNotices();
        $this->loadVideoPresentation();

        $this->loadPackshotAssets();
        $this->loadNoticeAssets();
        $this->loadVideoPresentationAssets();

        $this->indexAssets();
    }

    private function loadPackshots(): void
    {
        $ruleTemplate = [
            'product_selections' => [
                [
                    'field' => 'sku',
                    'operator'  => '=',
                    'value'     => '{{product_sku}}'
                ]
            ],
            'assign_assets_to'    => [
                [
                    'mode'  => 'set',
                    'attribute' => '{{linked_attribute}}'
                ]
            ]
        ];

        $packshot = AssetFamily::create(
            AssetFamilyIdentifier::fromString('packshot'),
            ['en_US' => 'Packshots'],
            Image::createEmpty(),
            RuleTemplateCollection::createFromProductLinkRules([$ruleTemplate])
        );

        $this->assetFamilyRepository->create($packshot);
        $order = 2;

        $description = TextAttribute::createTextarea(
            AttributeIdentifier::create('packshot', 'description', 'fingerprint'),
            AssetFamilyIdentifier::fromString('packshot'),
            AttributeCode::fromString('description'),
            LabelCollection::fromArray(['en_US' => 'Description']),
            AttributeOrder::fromInteger($order),
            AttributeIsRequired::fromBoolean(false),
            AttributeValuePerChannel::fromBoolean(false),
            AttributeValuePerLocale::fromBoolean(true),
            AttributeMaxLength::noLimit(),
            AttributeIsRichTextEditor::fromBoolean(true)
        );
        $order++;

        $datePublished = TextAttribute::createText(
            AttributeIdentifier::create('packshot', 'date_published', 'fingerprint'),
            AssetFamilyIdentifier::fromString('packshot'),
            AttributeCode::fromString('date_published'),
            LabelCollection::fromArray(['en_US' => 'Date Published']),
            AttributeOrder::fromInteger($order),
            AttributeIsRequired::fromBoolean(false),
            AttributeValuePerChannel::fromBoolean(false),
            AttributeValuePerLocale::fromBoolean(false),
            AttributeMaxLength::noLimit(),
            AttributeValidationRule::none(),
            AttributeRegularExpression::createEmpty()
        );
        $order++;

        $shootedBy = OptionAttribute::create(
            AttributeIdentifier::create('packshot', 'shooted_by', 'fingerprint'),
            AssetFamilyIdentifier::fromString('packshot'),
            AttributeCode::fromString('shooted_by'),
            LabelCollection::fromArray(['en_US' => 'Shooted By']),
            AttributeOrder::fromInteger($order),
            AttributeIsRequired::fromBoolean(true),
            AttributeValuePerChannel::fromBoolean(false),
            AttributeValuePerLocale::fromBoolean(false)
        );
        $order++;

        $original = MediaLinkAttribute::create(
            AttributeIdentifier::create('packshot', 'original', 'fingerprint'),
            AssetFamilyIdentifier::fromString('packshot'),
            AttributeCode::fromString('original'),
            LabelCollection::fromArray(['en_US' => 'Original']),
            AttributeOrder::fromInteger($order),
            AttributeIsRequired::fromBoolean(true),
            AttributeValuePerChannel::fromBoolean(false),
            AttributeValuePerLocale::fromBoolean(false),
            Prefix::fromString('https://custom-dam.com/packshots/'),
            Suffix::fromString('/original'),
            MediaType::fromString(MediaType::IMAGE)
        );
        $order++;

        $small = MediaLinkAttribute::create(
            AttributeIdentifier::create('packshot', 'small', 'fingerprint'),
            AssetFamilyIdentifier::fromString('packshot'),
            AttributeCode::fromString('small'),
            LabelCollection::fromArray(['en_US' => 'Small']),
            AttributeOrder::fromInteger($order),
            AttributeIsRequired::fromBoolean(false),
            AttributeValuePerChannel::fromBoolean(false),
            AttributeValuePerLocale::fromBoolean(false),
            Prefix::fromString('https://custom-dam.com/packshots/'),
            Suffix::fromString('/small'),
            MediaType::fromString(MediaType::IMAGE)
        );
        $order++;

        $linkedAttribute = TextAttribute::createText(
            AttributeIdentifier::create('packshot', 'linked_attribute', 'fingerprint'),
            AssetFamilyIdentifier::fromString('packshot'),
            AttributeCode::fromString('linked_attribute'),
            LabelCollection::fromArray(['en_US' => 'Linked Attribute']),
            AttributeOrder::fromInteger($order),
            AttributeIsRequired::fromBoolean(true),
            AttributeValuePerChannel::fromBoolean(false),
            AttributeValuePerLocale::fromBoolean(false),
            AttributeMaxLength::noLimit(),
            AttributeValidationRule::none(),
            AttributeRegularExpression::createEmpty()
        );
        $order++;

        $productSku = TextAttribute::createText(
            AttributeIdentifier::create('packshot', 'product_sku', 'fingerprint'),
            AssetFamilyIdentifier::fromString('packshot'),
            AttributeCode::fromString('product_sku'),
            LabelCollection::fromArray(['en_US' => 'Product SKU']),
            AttributeOrder::fromInteger($order),
            AttributeIsRequired::fromBoolean(true),
            AttributeValuePerChannel::fromBoolean(false),
            AttributeValuePerLocale::fromBoolean(false),
            AttributeMaxLength::noLimit(),
            AttributeValidationRule::fromString(AttributeValidationRule::REGULAR_EXPRESSION),
            AttributeRegularExpression::fromString('/^(\w-?)*/')
        );

        $this->attributeRepository->create($description);
        $this->attributeRepository->create($datePublished);
        $this->attributeRepository->create($shootedBy);
        $this->attributeRepository->create($original);
        $this->attributeRepository->create($small);
        $this->attributeRepository->create($linkedAttribute);
        $this->attributeRepository->create($productSku);
    }

    private function loadPackshotAssets()
    {
        $this->fixturesLoader
            ->asset('packshot', 'Philips22PDL4906H_pack')
            ->withValues([
                'description' => [
                    ['channel' => null, 'locale' => 'en_US', 'data' => 'Used technical ref only.']
                ],
                'date_published' => [
                    ['channel' => null, 'locale' => null, 'data' => '18/02/2018']
                ],
                'shooted_by' => [
                    ['channel' => null, 'locale' => null, 'data' => 'jean_jacques_photo']
                ],
                // These attributes are commented for now, to be uncommented once the front supports them
//                'original' => [
//                    ['channel' => null, 'locale' => null, 'data' => '22PDL4906H']
//                ],
//                'small' => [
//                    ['channel' => null, 'locale' => null, 'data' => '22PDL4906H']
//                ],
                'linked_attribute' => [
                    ['channel' => null, 'locale' => null, 'data' => 'packshot']
                ],
                'product_sku' => [
                    ['channel' => null, 'locale' => null, 'data' => '10638601']
                ],
            ])
            ->load();

        $this->fixturesLoader
            ->asset('packshot', 'iphone8_pack')
            ->withValues([
                'description' => [
                    ['channel' => null, 'locale' => 'en_US', 'data' => 'You should probably buy it.'],
                    ['channel' => null, 'locale' => 'fr_FR', 'data' => 'Vous devriez probablement l\'acheter.']
                ],
                'date_published' => [
                    ['channel' => null, 'locale' => null, 'data' => '18/05/2017']
                ],
                'shooted_by' => [
                    ['channel' => null, 'locale' => null, 'data' => 'michel_pellicule']
                ],
                // These attributes are commented for now, to be uncommented once the front supports them
//                'original' => [
//                    ['channel' => null, 'locale' => null, 'data' => 'iphone8']
//                ],
//                'small' => [
//                    ['channel' => null, 'locale' => null, 'data' => 'iphone8']
//                ],
                'linked_attribute' => [
                    ['channel' => null, 'locale' => null, 'data' => 'packshot']
                ],
                'product_sku' => [
                    ['channel' => null, 'locale' => null, 'data' => 'apple_iphone_8']
                ],
            ])
            ->load();

        $this->fixturesLoader
            ->asset('packshot', 'iphone7_pack')
            ->withValues([
                'description' => [
                    ['channel' => null, 'locale' => 'en_US', 'data' => 'You should probably buy it.'],
                    ['channel' => null, 'locale' => 'fr_FR', 'data' => 'Vous devriez probablement l\'acheter.']
                ],
                'date_published' => [
                    ['channel' => null, 'locale' => null, 'data' => '18/05/2017']
                ],
                'shooted_by' => [
                    ['channel' => null, 'locale' => null, 'data' => 'robert_photeau']
                ],
                // These attributes are commented for now, to be uncommented once the front supports them
//                'original' => [
//                    ['channel' => null, 'locale' => null, 'data' => 'iphone7']
//                ],
                'linked_attribute' => [
                    ['channel' => null, 'locale' => null, 'data' => 'packshot']
                ],
                'product_sku' => [
                    ['channel' => null, 'locale' => null, 'data' => 'apple_iphone_7']
                ],
            ])
            ->load();
    }

    private function loadNotices(): void
    {
        $notice = AssetFamily::create(
            AssetFamilyIdentifier::fromString('notice'),
            ['en_US' => 'Notices'],
            Image::createEmpty(),
            RuleTemplateCollection::empty()
        );

        $this->assetFamilyRepository->create($notice);
        $order = 2;

        $description = TextAttribute::createTextarea(
            AttributeIdentifier::create('notice', 'description', 'fingerprint'),
            AssetFamilyIdentifier::fromString('notice'),
            AttributeCode::fromString('description'),
            LabelCollection::fromArray(['en_US' => 'Description']),
            AttributeOrder::fromInteger($order),
            AttributeIsRequired::fromBoolean(false),
            AttributeValuePerChannel::fromBoolean(false),
            AttributeValuePerLocale::fromBoolean(true),
            AttributeMaxLength::noLimit(),
            AttributeIsRichTextEditor::fromBoolean(true)
        );
        $order++;

        $targetCountries = OptionCollectionAttribute::create(
            AttributeIdentifier::create('notice', 'target_countries', 'fingerprint'),
            AssetFamilyIdentifier::fromString('notice'),
            AttributeCode::fromString('target_countries'),
            LabelCollection::fromArray(['en_US' => 'Target Countries']),
            AttributeOrder::fromInteger($order),
            AttributeIsRequired::fromBoolean(true),
            AttributeValuePerChannel::fromBoolean(false),
            AttributeValuePerLocale::fromBoolean(false)
        );
        $order++;

        $datePublished = TextAttribute::createText(
            AttributeIdentifier::create('notice', 'date_published', 'fingerprint'),
            AssetFamilyIdentifier::fromString('notice'),
            AttributeCode::fromString('date_published'),
            LabelCollection::fromArray(['en_US' => 'Date Published']),
            AttributeOrder::fromInteger($order),
            AttributeIsRequired::fromBoolean(false),
            AttributeValuePerChannel::fromBoolean(false),
            AttributeValuePerLocale::fromBoolean(false),
            AttributeMaxLength::noLimit(),
            AttributeValidationRule::none(),
            AttributeRegularExpression::createEmpty()
        );
        $order++;

        $original = MediaLinkAttribute::create(
            AttributeIdentifier::create('notice', 'original', 'fingerprint'),
            AssetFamilyIdentifier::fromString('notice'),
            AttributeCode::fromString('original'),
            LabelCollection::fromArray(['en_US' => 'Original']),
            AttributeOrder::fromInteger($order),
            AttributeIsRequired::fromBoolean(true),
            AttributeValuePerChannel::fromBoolean(false),
            AttributeValuePerLocale::fromBoolean(false),
            Prefix::fromString('https://custom-dam.com/pdf-notices/'),
            Suffix::fromString('/original'),
            MediaType::fromString(MediaType::OTHER)
        );
        $order++;

        $linkedAttribute = TextAttribute::createText(
            AttributeIdentifier::create('notice', 'linked_attribute', 'fingerprint'),
            AssetFamilyIdentifier::fromString('notice'),
            AttributeCode::fromString('linked_attribute'),
            LabelCollection::fromArray(['en_US' => 'Linked Attribute']),
            AttributeOrder::fromInteger($order),
            AttributeIsRequired::fromBoolean(true),
            AttributeValuePerChannel::fromBoolean(false),
            AttributeValuePerLocale::fromBoolean(false),
            AttributeMaxLength::noLimit(),
            AttributeValidationRule::none(),
            AttributeRegularExpression::createEmpty()
        );
        $order++;

        $productSku = TextAttribute::createText(
            AttributeIdentifier::create('notice', 'product_sku', 'fingerprint'),
            AssetFamilyIdentifier::fromString('notice'),
            AttributeCode::fromString('product_sku'),
            LabelCollection::fromArray(['en_US' => 'Product SKU']),
            AttributeOrder::fromInteger($order),
            AttributeIsRequired::fromBoolean(true),
            AttributeValuePerChannel::fromBoolean(false),
            AttributeValuePerLocale::fromBoolean(false),
            AttributeMaxLength::noLimit(),
            AttributeValidationRule::fromString(AttributeValidationRule::REGULAR_EXPRESSION),
            AttributeRegularExpression::fromString('/^(\w-?)*/')
        );

        $this->attributeRepository->create($description);
        $this->attributeRepository->create($targetCountries);
        $this->attributeRepository->create($datePublished);
        $this->attributeRepository->create($original);
        $this->attributeRepository->create($linkedAttribute);
        $this->attributeRepository->create($productSku);
    }

    private function loadNoticeAssets(): void
    {
        $this->fixturesLoader
            ->asset('notice', 'Philips22PDL4906H_notice')
            ->withValues([
                'description' => [
                    ['channel' => null, 'locale' => 'en_US', 'data' => 'Used technical ref only.']
                ],
                'target_countries' => [
                    ['channel' => null, 'locale' => null, 'data' => ['united_kingdom', 'united_states', 'ireland']]
                ],
                'date_published' => [
                    ['channel' => null, 'locale' => null, 'data' => '02/05/2018']
                ],
                // These attributes are commented for now, to be uncommented once the front supports them
//                'original' => [
//                    ['channel' => null, 'locale' => null, 'data' => '22PDL4906H']
//                ],
                'linked_attribute' => [
                    ['channel' => null, 'locale' => null, 'data' => 'notice']
                ],
                'product_sku' => [
                    ['channel' => null, 'locale' => null, 'data' => '10638601']
                ],
            ])
            ->load();

        $this->fixturesLoader
            ->asset('notice', 'av36')
            ->withValues([
                'description' => [
                    ['channel' => null, 'locale' => 'en_US', 'data' => 'French technical notice of the Avision AV36.']
                ],
                'target_countries' => [
                    ['channel' => null, 'locale' => null, 'data' => ['france']]
                ],
                'date_published' => [
                    ['channel' => null, 'locale' => null, 'data' => '22/02/1990']
                ],
                // These attributes are commented for now, to be uncommented once the front supports them
//                'original' => [
//                    ['channel' => null, 'locale' => null, 'data' => 'av36']
//                ],
                'linked_attribute' => [
                    ['channel' => null, 'locale' => null, 'data' => 'notice']
                ],
                'product_sku' => [
                    ['channel' => null, 'locale' => null, 'data' => '12249740']
                ],
            ])
            ->load();
    }

    private function loadVideoPresentation(): void
    {
        $ruleTemplateToAdd = [
            'product_selections' => [
                [
                    'field'    => 'sku',
                    'operator' => '=',
                    'value'    => '{{product_sku}}'
                ]
            ],
            'assign_assets_to' => [
                [
                    'mode'      => 'add',
                    'attribute' => '{{linked_attribute}}'
                ]
            ]
        ];
        $ruleTemplateToSet = [
            'product_selections' => [
                [
                    'field'    => 'sku',
                    'operator' => '=',
                    'value'    => '{{product_sku}}'
                ]
            ],
            'assign_assets_to' => [
                [
                    'mode'      => 'set',
                    'attribute' => '{{linked_attribute}}'
                ]
            ]
        ];
        $videoPresentation = AssetFamily::create(
            AssetFamilyIdentifier::fromString('video_presentation'),
            ['en_US' => 'Video Presentation'],
            Image::createEmpty(),
            RuleTemplateCollection::createFromProductLinkRules([$ruleTemplateToAdd, $ruleTemplateToSet])
        );

        $this->assetFamilyRepository->create($videoPresentation);
        $order = 2;

        $videoTranscription = TextAttribute::createTextarea(
            AttributeIdentifier::create('video_presentation', 'video_transcription', 'fingerprint'),
            AssetFamilyIdentifier::fromString('video_presentation'),
            AttributeCode::fromString('video_transcription'),
            LabelCollection::fromArray(['en_US' => 'Video Transcription']),
            AttributeOrder::fromInteger($order),
            AttributeIsRequired::fromBoolean(false),
            AttributeValuePerChannel::fromBoolean(false),
            AttributeValuePerLocale::fromBoolean(false),
            AttributeMaxLength::noLimit(),
            AttributeIsRichTextEditor::fromBoolean(false)
        );
        $order++;

        $original = MediaLinkAttribute::create(
            AttributeIdentifier::create('video_presentation', 'original', 'fingerprint'),
            AssetFamilyIdentifier::fromString('video_presentation'),
            AttributeCode::fromString('original'),
            LabelCollection::fromArray(['en_US' => 'Original']),
            AttributeOrder::fromInteger($order),
            AttributeIsRequired::fromBoolean(true),
            AttributeValuePerChannel::fromBoolean(false),
            AttributeValuePerLocale::fromBoolean(false),
            Prefix::fromString('https://custom-dam.com/video/presentation'),
            Suffix::empty(),
            MediaType::fromString(MediaType::OTHER)
        );
        $order++;

        $linkedAttribute = TextAttribute::createText(
            AttributeIdentifier::create('video_presentation', 'linked_attribute', 'fingerprint'),
            AssetFamilyIdentifier::fromString('video_presentation'),
            AttributeCode::fromString('linked_attribute'),
            LabelCollection::fromArray(['en_US' => 'Linked Attribute']),
            AttributeOrder::fromInteger($order),
            AttributeIsRequired::fromBoolean(true),
            AttributeValuePerChannel::fromBoolean(false),
            AttributeValuePerLocale::fromBoolean(false),
            AttributeMaxLength::noLimit(),
            AttributeValidationRule::none(),
            AttributeRegularExpression::createEmpty()
        );
        $order++;

        $productSku = TextAttribute::createText(
            AttributeIdentifier::create('video_presentation', 'product_sku', 'fingerprint'),
            AssetFamilyIdentifier::fromString('video_presentation'),
            AttributeCode::fromString('product_sku'),
            LabelCollection::fromArray(['en_US' => 'Product SKU']),
            AttributeOrder::fromInteger($order),
            AttributeIsRequired::fromBoolean(true),
            AttributeValuePerChannel::fromBoolean(false),
            AttributeValuePerLocale::fromBoolean(false),
            AttributeMaxLength::noLimit(),
            AttributeValidationRule::fromString(AttributeValidationRule::REGULAR_EXPRESSION),
            AttributeRegularExpression::fromString('/^(\w-?)*/')
        );

        $this->attributeRepository->create($videoTranscription);
        $this->attributeRepository->create($original);
        $this->attributeRepository->create($linkedAttribute);
        $this->attributeRepository->create($productSku);
    }

    private function loadVideoPresentationAssets(): void
    {
        $this->fixturesLoader
            ->asset('video_presentation', 'Philips22PDL4906H_video')
            ->withValues([
                'video_transcription' => [
                    ['channel' => null, 'locale' => null, 'data' => 'Philips, a new generation.']
                ],
                // These attributes are commented for now, to be uncommented once the front supports them
//                'original' => [
//                    ['channel' => null, 'locale' => null, 'data' => '22PDL4906H']
//                ],
                'linked_attribute' => [
                    ['channel' => null, 'locale' => null, 'data' => 'video']
                ],
                'product_sku' => [
                    ['channel' => null, 'locale' => null, 'data' => '10638601']
                ],
            ])
            ->load();
    }

    private function uploadImage($code): FileInfoInterface
    {
        $path = sprintf('/../../Resources/fixtures/files/%s.jpg', $code);
        $rawFile = new \SplFileInfo(__DIR__ . $path);

        return $this->storer->store($rawFile, self::CATALOG_STORAGE_ALIAS);
    }

    private function indexAssets(): void
    {
        $this->assetClient->resetIndex();
        $this->commandLauncher->executeForeground(
            sprintf('%s %s', IndexAssetsCommand::INDEX_ASSETS_COMMAND_NAME, '--all')
        );
        $this->assetClient->refreshIndex();
    }
}
