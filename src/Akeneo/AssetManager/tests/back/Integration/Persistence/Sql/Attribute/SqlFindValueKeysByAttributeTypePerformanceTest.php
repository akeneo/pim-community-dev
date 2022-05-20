<?php

declare(strict_types=1);

namespace Akeneo\AssetManager\Integration\Persistence\Sql\Attribute;

use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamily;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Model\AssetFamily\RuleTemplateCollection;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeCode;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeIsReadOnly;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeIsRequired;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeMaxLength;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeOrder;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeRegularExpression;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeValidationRule;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeValuePerChannel;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeValuePerLocale;
use Akeneo\AssetManager\Domain\Model\Attribute\TextAttribute;
use Akeneo\AssetManager\Domain\Model\Image;
use Akeneo\AssetManager\Domain\Model\LabelCollection;
use Akeneo\AssetManager\Domain\Query\Attribute\FindValueKeysByAttributeTypeInterface;
use Akeneo\AssetManager\Integration\SqlIntegrationTestCase;

class SqlFindValueKeysByAttributeTypePerformanceTest extends SqlIntegrationTestCase
{
    private FindValueKeysByAttributeTypeInterface $findValueKeysByAttributeType;

    private int $order = 2;

    public function setUp(): void
    {
        parent::setUp();

        $this->findValueKeysByAttributeType = $this->get('akeneo_assetmanager.infrastructure.persistence.query.find_value_keys_by_attribute_type');
        $this->connection = $this->get('database_connection');
//        $this->resetDB();
//        $this->loadCategories();
//        $this->loadLocales();
//        $this->loadChannels();
//        $this->loadAssetFamily();
//        $this->loadAssetAttributes();
    }

    /**
     * @test
     */
    public function it_takes_less_than_300ms()
    {
        $designer = AssetFamilyIdentifier::fromString('designer');

        for ($i = 1; $i <= 3; $i++) {
            $startTime = microtime(true);
            $this->findValueKeysByAttributeType->find($designer, ['text']);
            $endTime = microtime(true);
            $time = $endTime - $startTime;
            dump('Total : ' . $time);
            dump('');
        }

//        $this->assertLessThan(100, $time);
    }

    private function resetDB(): void
    {
        $resetQuery = <<<SQL
            SET foreign_key_checks = 0;

            DELETE FROM akeneo_batch_job_execution;
            DELETE FROM akeneo_batch_step_execution;
            DELETE FROM akeneo_batch_warning;
            DELETE FROM akeneo_asset_manager_attribute;
            DELETE FROM akeneo_asset_manager_asset;
            DELETE FROM akeneo_asset_manager_asset_family;
            DELETE FROM akeneo_asset_manager_asset_family_permissions;
            DELETE FROM pim_catalog_attribute_group;
            DELETE FROM pim_catalog_attribute;
            DELETE FROM oro_user;
            DELETE FROM oro_access_group;
            DELETE FROM oro_user_access_group;
            DELETE FROM pim_catalog_channel;
            DELETE FROM pim_catalog_channel_locale;
            DELETE FROM pim_catalog_category;
            DELETE FROM pim_catalog_locale;
            DELETE FROM akeneo_file_storage_file_info;

            SET foreign_key_checks = 1;
SQL;
        $this->connection->executeQuery($resetQuery);
    }

    private function loadCategories(): void
    {
        $resetCategory = <<<SQL
            INSERT INTO `pim_catalog_category` (`id`, `parent_id`, `code`, `created`, `root`, `lvl`, `lft`, `rgt`)
            VALUES (1, NULL, 'master', '2022-01-01 12:00:30', 1, 0, 1, 2);
        SQL;
        $this->connection->executeQuery($resetCategory);
    }

    private function loadLocales(): void
    {
        $randomLocales = [];
        for ($i = 1; $i <= 50; $i++) {
            $randomLocaleCode = substr(str_shuffle('ABCDEFGHIJKLMNOPQRSTUVWXYZ'),1, 5);
            $randomLocales[] = sprintf("(%d, '%s', 1)", $i, $randomLocaleCode);
        }

        $localeQuery = <<<SQL
            INSERT INTO `pim_catalog_locale` (`id`, `code`, `is_activated`)
            VALUES %s
        SQL;
        $this->connection->executeQuery(sprintf($localeQuery, implode(',', $randomLocales)));
    }

    private function loadChannels(): void
    {
        $randomChannels = [];
        for ($i = 1; $i <= 20; $i++) {
            $randomChannelCode = substr(str_shuffle('ABCDEFGHIJKLMNOPQRSTUVWXYZ'),1, 5);
            $randomChannels[] = sprintf("(%d, 1, '%s', 'a:0:{}')", $i, $randomChannelCode);
        }

        $channelQuery = <<<SQL
            INSERT INTO `pim_catalog_channel` (`id`, `category_id`, `code`, `conversionUnits`)
            VALUES %s
        SQL;

        $this->connection->executeQuery(sprintf($channelQuery, implode(',', $randomChannels)));

        for ($i = 1; $i <= 50; $i++) {
            for ($j = 1; $j <= 20; $j++) {
                $channelsLocales[] = "($j, $i)";
            }
        }

        $channelLocaleQuery = <<<SQL
            INSERT INTO `pim_catalog_channel_locale` (`channel_id`, `locale_id`)
            VALUES %s
        SQL;

        $this->connection->executeQuery(sprintf($channelLocaleQuery, implode(',', $channelsLocales)));
    }

    private function loadAssetFamily(): void
    {
        $assetFamilyRepository = $this->get('akeneo_assetmanager.infrastructure.persistence.repository.asset_family');
        $assetFamily = AssetFamily::create(
            AssetFamilyIdentifier::fromString('designer'),
            [
                'fr_FR' => 'Concepteur',
                'en_US' => 'Designer',
            ],
            Image::createEmpty(),
            RuleTemplateCollection::empty()
        );
        $assetFamilyRepository->create($assetFamily);
    }

    private function loadAssetAttributes(): void
    {
        $assetFamilyIdentifier = AssetFamilyIdentifier::fromString('designer');
        $attributeRepository = $this->get('akeneo_assetmanager.infrastructure.persistence.repository.attribute');

        for ($i = 1; $i <= 100; $i++) {
            $attrCode = AttributeCode::fromString(sprintf('text_attr_%d', $i));
            $identifier = $attributeRepository->nextIdentifier($assetFamilyIdentifier, $attrCode);
            $textAttribute = TextAttribute::createText(
                $identifier,
                $assetFamilyIdentifier,
                $attrCode,
                LabelCollection::fromArray(['fr_FR' => 'dummy label']),
                AttributeOrder::fromInteger($this->order++),
                AttributeIsRequired::fromBoolean(false),
                AttributeIsReadOnly::fromBoolean(false),
                AttributeValuePerChannel::fromBoolean(true),
                AttributeValuePerLocale::fromBoolean(true),
                AttributeMaxLength::fromInteger(25),
                AttributeValidationRule::none(),
                AttributeRegularExpression::createEmpty()
            );

            $attributeRepository->create($textAttribute);
        }
    }
}
