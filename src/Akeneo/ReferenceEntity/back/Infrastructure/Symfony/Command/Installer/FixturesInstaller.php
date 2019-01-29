<?php

declare(strict_types=1);

namespace Akeneo\ReferenceEntity\Infrastructure\Symfony\Command\Installer;

use Akeneo\ReferenceEntity\Infrastructure\Symfony\Command\IndexRecordsCommand;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;
use Akeneo\Tool\Component\Console\CommandLauncher;
use Akeneo\Tool\Component\FileStorage\File\FileStorerInterface;
use Akeneo\Tool\Component\FileStorage\Model\FileInfoInterface;
use Doctrine\DBAL\Connection;
use Ramsey\Uuid\Uuid;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 */
class FixturesInstaller
{
    public const ICE_CAT_DEMO_DEV_CATALOG = 'PimEnterpriseInstallerBundle:icecat_demo_dev';
    private const CATALOG_STORAGE_ALIAS = 'catalogStorage';
    private const NUMBER_OF_FAKE_RECORD_TO_CREATE = 10000;

    /** @var Connection */
    private $sqlConnection;

    /** @var FileStorerInterface */
    private $storer;

    /** @var Client */
    private $recordClient;

    /** @var CommandLauncher */
    private $commandLauncher;

    public function __construct(
        Connection $sqlConnection,
        FileStorerInterface $storer,
        Client $recordClient,
        CommandLauncher $commandLauncher
    ) {
        $this->sqlConnection = $sqlConnection;
        $this->storer = $storer;
        $this->recordClient = $recordClient;
        $this->commandLauncher = $commandLauncher;
    }

    public function createSchema(): void
    {
        $sql = <<<SQL
SET foreign_key_checks = 0;
DROP TABLE IF EXISTS `akeneo_reference_entity_attribute`;
DROP TABLE IF EXISTS `akeneo_reference_entity_record`;
DROP TABLE IF EXISTS `akeneo_reference_entity_reference_entity_permissions`;
DROP TABLE IF EXISTS `akeneo_reference_entity_reference_entity`;

CREATE TABLE `akeneo_reference_entity_reference_entity` (
    `identifier` VARCHAR(255) NOT NULL,
    `labels` JSON NOT NULL,
    `image` VARCHAR(255) NULL,
    `attribute_as_label` VARCHAR(255) NULL,
    `attribute_as_image` VARCHAR(255) NULL,
    PRIMARY KEY (`identifier`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE akeneo_reference_entity_record (
    identifier VARCHAR(255) NOT NULL,
    code VARCHAR(255) NOT NULL,
    reference_entity_identifier VARCHAR(255) NOT NULL,
    value_collection JSON NOT NULL,
    PRIMARY KEY (identifier),
    UNIQUE akeneoreference_entity_identifier_record_ux (reference_entity_identifier, code),
    CONSTRAINT akeneoreference_entity_reference_entity_identifier_foreign_key FOREIGN KEY (reference_entity_identifier) REFERENCES akeneo_reference_entity_reference_entity (identifier)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `akeneo_reference_entity_attribute` (
    `identifier` VARCHAR(255) NOT NULL,
    `code` VARCHAR(255) NOT NULL,
    `reference_entity_identifier` VARCHAR(255) NOT NULL,
    `labels` JSON NOT NULL,
    `attribute_type` VARCHAR(255) NOT NULL,
    `attribute_order` INT NOT NULL,
    `is_required` BOOLEAN NOT NULL,
    `value_per_channel` BOOLEAN NOT NULL,
    `value_per_locale` BOOLEAN NOT NULL,
    `additional_properties` JSON NOT NULL,
    PRIMARY KEY (`identifier`),
    UNIQUE `attribute_identifier_index` (`code`, `reference_entity_identifier`),
    UNIQUE `attribute_reference_entity_order_index` (`reference_entity_identifier`, `attribute_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `akeneo_reference_entity_reference_entity_permissions` (
    `reference_entity_identifier` VARCHAR(255) NOT NULL,
    `user_group_identifier` SMALLINT(6) NOT NULL,
    `right_level` VARCHAR(255) NOT NULL,
    PRIMARY KEY (`reference_entity_identifier`, `user_group_identifier`),
    CONSTRAINT permissions_reference_entity_identifier_foreign_key FOREIGN KEY (`reference_entity_identifier`) REFERENCES `akeneo_reference_entity_reference_entity` (identifier)
      ON DELETE CASCADE,
    CONSTRAINT user_group_foreign_key FOREIGN KEY (`user_group_identifier`) REFERENCES `oro_access_group` (id)
      ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE `akeneo_reference_entity_reference_entity` 
    ADD CONSTRAINT akeneoreference_entity_attribute_as_label_foreign_key 
    FOREIGN KEY (attribute_as_label) 
    REFERENCES akeneo_reference_entity_attribute (identifier)
    ON DELETE SET NULL;
    
ALTER TABLE `akeneo_reference_entity_reference_entity`         
    ADD CONSTRAINT akeneoreference_entity_attribute_as_image_foreign_key 
    FOREIGN KEY (attribute_as_image) 
    REFERENCES akeneo_reference_entity_attribute (identifier)
    ON DELETE SET NULL;
    
ALTER TABLE `akeneo_reference_entity_attribute`         
    ADD CONSTRAINT attribute_reference_entity_identifier_foreign_key 
    FOREIGN KEY (`reference_entity_identifier`) 
    REFERENCES `akeneo_reference_entity_reference_entity` (identifier)
    ON DELETE CASCADE;
    
SET foreign_key_checks = 1;
SQL;
        $this->sqlConnection->exec($sql);
        $this->recordClient->resetIndex();
    }

    /**
     * In order to correctly escape values of records, escape as follow:
     * \n => \\\\n
     * ' => \\'
     * " => \\\"
     */
    public function loadCatalog(string $catalogName): void
    {
        if (self::ICE_CAT_DEMO_DEV_CATALOG !== $catalogName) {
            return;
        }

        $this->loadCities();
        $this->loadFakeCities();
        $this->loadMainColors();
        $this->loadDesigners();
        $this->loadBrands();
        $this->loadColors();
        $this->loadCountries();
        $this->loadMaterials();
        $this->indexRecords();
    }

    private function loadCities(): void
    {
        $city = $this->uploadImage('city')->getKey();
        $roma = $this->uploadImage('roma')->getKey();
        $lisbon = $this->uploadImage('lisbon')->getKey();
        $cannes = $this->uploadImage('Cannes')->getKey();
        $paris = $this->uploadImage('Paris')->getKey();
        $newYork = $this->uploadImage('newyork')->getKey();
        $sql = <<<SQL
SET foreign_key_checks = 0;
INSERT INTO `akeneo_reference_entity_reference_entity` (`identifier`, `labels`, `image`, `attribute_as_label`, `attribute_as_image`)
VALUES ('city','{\"en_US\": \"City\"}', '${city}', 'label_city_491b2b80-474a-4254-a4ef-5f12ba30d6fc', 'image_city_491b2b80-474a-4254-a4ef-5f12ba30d6d');

INSERT INTO `akeneo_reference_entity_attribute` (
  `identifier`,
  `code`,
  `reference_entity_identifier`,
  `labels`,
  `attribute_type`,
  `attribute_order`,
  `is_required`,
  `value_per_channel`,
  `value_per_locale`,
  `additional_properties`
  )
VALUES
  ('label_city_491b2b80-474a-4254-a4ef-5f12ba30d6fc','label','city','{\"en_US\": \"Label\"}','text',1,0,0,1,'{\"max_length\": null, \"is_textarea\": false, \"validation_rule\": \"none\", \"regular_expression\": null, \"is_rich_text_editor\": false}'),
  ('image_city_491b2b80-474a-4254-a4ef-5f12ba30d6d','image','city','{\"en_US\": \"Image\"}','image',2,0,0,0,'{\"max_file_size\": \"10\", \"allowed_extensions\": [\"jpeg\", \"jpg\", \"png\"]}'),
  ('description_city_491b2b80-474a-4254-a4ef-5f12ba30d6fc','description','city','{\"en_US\": \"Description\"}','text',3,0,0,1,'{\"max_length\": null, \"is_textarea\": true, \"validation_rule\": \"none\", \"regular_expression\": null, \"is_rich_text_editor\": true}'),
  ('timezone_city_9944f4d6-06c0-484a-8a86-6fe32eefce7d','timezone','city','{\"en_US\": \"Timezone\"}','text',4,0,0,0,'{\"max_length\": null, \"is_textarea\": false, \"validation_rule\": \"none\", \"regular_expression\": null, \"is_rich_text_editor\": false}'),
  ('region_city_d703b6ad-8a63-40a6-8771-f9b8c9567168','region','city','{\"en_US\": \"Region\"}','text',5,0,0,0,'{\"max_length\": null, \"is_textarea\": false, \"validation_rule\": \"none\", \"regular_expression\": null, \"is_rich_text_editor\": false}'),
  ('weather_city_65ccca01-fcb9-4a5c-ab63-a8a04ef6e2ed','weather','city','{\"en_US\": \"Weather\"}','text',6,0,0,0,'{\"max_length\": null, \"is_textarea\": false, \"validation_rule\": \"none\", \"regular_expression\": null, \"is_rich_text_editor\": false}'),
  ('country_city_29aea250-bc94-49b2-8259-bbc116410eb2','country','city','{\"en_US\": \"Country\"}','text',7,0,0,0,'{\"max_length\": null, \"is_textarea\": false, \"validation_rule\": \"none\", \"regular_expression\": null, \"is_rich_text_editor\": false}')
;
INSERT INTO `akeneo_reference_entity_record` (`identifier`, `code`, `reference_entity_identifier`, `value_collection`)
VALUES
  ('city_roma_ee07911a-cd91-426c-89f0-5525c26f7467','roma','city','{"image_city_491b2b80-474a-4254-a4ef-5f12ba30d6d": {"data": {"size": 5396, "filePath": "${roma}", "mimeType": "image/jpeg", "extension": "jpeg", "originalFilename": "roma.jpg"}, "locale": null, "channel": null, "attribute": "image_city_491b2b80-474a-4254-a4ef-5f12ba30d6d"}, "label_city_491b2b80-474a-4254-a4ef-5f12ba30d6fc_en_US": {"data": "Roma", "locale": "en_US", "channel": null, "attribute":"label_city_491b2b80-474a-4254-a4ef-5f12ba30d6fc"},"region_city_d703b6ad-8a63-40a6-8771-f9b8c9567168": {"data": "Lazio", "locale": null, "channel": null, "attribute": "region_city_d703b6ad-8a63-40a6-8771-f9b8c9567168"}, "country_city_29aea250-bc94-49b2-8259-bbc116410eb2": {"data": "Italia", "locale": null, "channel": null, "attribute": "country_city_29aea250-bc94-49b2-8259-bbc116410eb2"}, "weather_city_65ccca01-fcb9-4a5c-ab63-a8a04ef6e2ed": {"data": "26°C, Wind NW at 8 km/h, 34% Humidity", "locale": null, "channel": null, "attribute": "weather_city_65ccca01-fcb9-4a5c-ab63-a8a04ef6e2ed"}, "timezone_city_9944f4d6-06c0-484a-8a86-6fe32eefce7d": {"data": "Central European Summer Time", "locale": null, "channel": null, "attribute": "timezone_city_9944f4d6-06c0-484a-8a86-6fe32eefce7d"}, "description_city_491b2b80-474a-4254-a4ef-5f12ba30d6fc_en_US": {"data": "<p><strong>Looking for a getaway in the Eternal City?</strong>&nbsp;</p>\\\\n<p>Rome opens its doors. ?</p>\\\\n<p>Discover the archaeological, architectural and artistic wonders that make Rome one of the most popular cities in Europe for centuries. Between the \\\"Seven Hills\\\", from the Capitol to the Palatine, visit the must-see sights, admire the masterpieces of the past, stroll through the charming alleys, enjoy generous cuisine or a tasty Ristretto, treat yourself to a shopping break. .</p>\\\\n<img src=\\\"https://images1.bovpg.net/r/back/fr/sale/d786359a7ad0b0.jpg\\\" alt=\\\"undefined\\\" style=\\\"float:left;height: auto;width: auto\\\"/>\\\\n<p></p>\\\\n<p><strong>Adopt the Dolce Vita lifestyle!</strong></p>\\\\n", "locale": "en_US", "channel": null, "attribute": "description_city_491b2b80-474a-4254-a4ef-5f12ba30d6fc"}}')
  ,('city_lisbon_6198d4cd-342f-4d75-9261-f862a9a23b18','lisbon','city','{"image_city_491b2b80-474a-4254-a4ef-5f12ba30d6d": {"data": {"size": 5396, "filePath": "${lisbon}", "mimeType": "image/jpeg", "extension": "jpeg", "originalFilename": "lisbon.jpg"}, "locale": null, "channel": null, "attribute": "image_city_491b2b80-474a-4254-a4ef-5f12ba30d6d"}, "label_city_491b2b80-474a-4254-a4ef-5f12ba30d6fc_en_US": {"data": "Lison", "locale": "en_US", "channel": null, "attribute":"label_city_491b2b80-474a-4254-a4ef-5f12ba30d6fc"}, "region_city_d703b6ad-8a63-40a6-8771-f9b8c9567168": {"data": "Lisbon", "locale": null, "channel": null, "attribute": "region_city_d703b6ad-8a63-40a6-8771-f9b8c9567168"}, "country_city_29aea250-bc94-49b2-8259-bbc116410eb2": {"data": "Portugal", "locale": null, "channel": null, "attribute": "country_city_29aea250-bc94-49b2-8259-bbc116410eb2"}, "weather_city_65ccca01-fcb9-4a5c-ab63-a8a04ef6e2ed": {"data": "28°C, Wind E at 6 km/h, 19% Humidity", "locale": null, "channel": null, "attribute": "weather_city_65ccca01-fcb9-4a5c-ab63-a8a04ef6e2ed"}, "timezone_city_9944f4d6-06c0-484a-8a86-6fe32eefce7d": {"data": "Western European Summer Time", "locale": null, "channel": null, "attribute": "timezone_city_9944f4d6-06c0-484a-8a86-6fe32eefce7d"}, "description_city_491b2b80-474a-4254-a4ef-5f12ba30d6fc_en_US": {"data": "<p><strong>Want to live a charming getaway?</strong></p>\\\\n<p>Forget Venice and discover Lisbon! You will fall for THE trendy and romantic city trip ... Lisbon will charm you with its history, architecture, culture and people. Be enchanted by the soul of Lisbon, strolling through the steep streets with colorful facades of Alfama or Bairro Alto. Admire the sublime views of the Tagus and the Atlantic Ocean from the viewpoints accessible by yellow tram ... There is so much to discover in Lisbon and so little to do to escape the time of a weekend full of poetry . Do not wait any longer !</p>\\\\n<p></p>\\\\n<iframe width=\\\"auto\\\" height=\\\"auto\\\" src=\\\"https://www.youtube.com/embed/Tax-Dhbkq6c\\\" frameBorder=\\\"0\\\"></iframe>\\\\n<p></p>\\\\n", "locale": "en_US", "channel": null, "attribute": "description_city_491b2b80-474a-4254-a4ef-5f12ba30d6fc"}}')
  ,('city_cannes_01afdc3e-3ecf-4a86-85ef-e81b2d6e95fd','cannes','city','{"image_city_491b2b80-474a-4254-a4ef-5f12ba30d6d": {"data": {"size": 5396, "filePath": "${cannes}", "mimeType": "image/jpeg", "extension": "jpeg", "originalFilename": "cannes.jpg"}, "locale": null, "channel": null, "attribute": "image_city_491b2b80-474a-4254-a4ef-5f12ba30d6d"}, "label_city_491b2b80-474a-4254-a4ef-5f12ba30d6fc_en_US": {"data": "Cannes", "locale": "en_US", "channel": null, "attribute":"label_city_491b2b80-474a-4254-a4ef-5f12ba30d6fc"}, "country_city_29aea250-bc94-49b2-8259-bbc116410eb2": {"data": "France", "locale": null, "channel": null, "attribute": "country_city_29aea250-bc94-49b2-8259-bbc116410eb2"}}')
  ,('city_paris_42b05a3c-e811-47ce-88fe-21aa03479c2f','paris','city','{"image_city_491b2b80-474a-4254-a4ef-5f12ba30d6d": {"data": {"size": 5396, "filePath": "${paris}", "mimeType": "image/jpeg", "extension": "jpeg", "originalFilename": "paris.jpg"}, "locale": null, "channel": null, "attribute": "image_city_491b2b80-474a-4254-a4ef-5f12ba30d6d"}, "label_city_491b2b80-474a-4254-a4ef-5f12ba30d6fc_en_US": {"data": "Paris", "locale": "en_US", "channel": null, "attribute":"label_city_491b2b80-474a-4254-a4ef-5f12ba30d6fc"}, "country_city_29aea250-bc94-49b2-8259-bbc116410eb2": {"data": "France", "locale": null, "channel": null, "attribute": "country_city_29aea250-bc94-49b2-8259-bbc116410eb2"}}')
  ,('city_new_york_b5150405-4cd6-4743-905f-641d4191d16d','new_york','city','{"image_city_491b2b80-474a-4254-a4ef-5f12ba30d6d": {"data": {"size": 5396, "filePath": "${newYork}", "mimeType": "image/jpeg", "extension": "jpeg", "originalFilename": "roma.jpg"}, "locale": null, "channel": null, "attribute": "image_city_491b2b80-474a-4254-a4ef-5f12ba30d6d"}, "label_city_491b2b80-474a-4254-a4ef-5f12ba30d6fc_en_US": {"data": "New York", "locale": "en_US", "channel": null, "attribute":"label_city_491b2b80-474a-4254-a4ef-5f12ba30d6fc"}}')
;
SET foreign_key_checks = 1;
SQL;
        $other = <<<SQL

SQL;

        $this->sqlConnection->executeUpdate($sql);
    }

    private function loadFakeCities(): void
    {
        $baseSql = <<<SQL
INSERT INTO `akeneo_reference_entity_record` (`identifier`, `code`, `reference_entity_identifier`, `value_collection`)
VALUES
%s;
SQL;
        $fakeCities = [];
        for ($i = 1; $i < self::NUMBER_OF_FAKE_RECORD_TO_CREATE; $i++) {
            if (0 === ($i % 2000)) {
                $this->sqlConnection->executeUpdate(sprintf($baseSql, implode($fakeCities, ',')));
                $fakeCities = [];
            }
            $fakeCities[] = $this->generateFakeCity();
        }
        if (!empty($fakeCities)) {
            $update = sprintf($baseSql, implode($fakeCities, ','));
            $this->sqlConnection->executeUpdate($update);
        }
    }

    private function generateFakeCity(): string
    {
        $fakeCity = <<<SQL
('city_%s_%s','%s%s','city','{"label_city_491b2b80-474a-4254-a4ef-5f12ba30d6fc_en_US": {"data": "%s", "locale": "en_US", "channel": null, "attribute":"label_city_491b2b80-474a-4254-a4ef-5f12ba30d6fc"},"region_city_d703b6ad-8a63-40a6-8771-f9b8c9567168": {"data": "%s", "locale": null, "channel": null, "attribute": "region_city_d703b6ad-8a63-40a6-8771-f9b8c9567168"}, "country_city_29aea250-bc94-49b2-8259-bbc116410eb2": {"data": "France", "locale": null, "channel": null, "attribute": "country_city_29aea250-bc94-49b2-8259-bbc116410eb2"}, "weather_city_65ccca01-fcb9-4a5c-ab63-a8a04ef6e2ed": {"data": "%s°C, Wind E at %s km/h, 19 Humidity", "locale": null, "channel": null, "attribute": "weather_city_65ccca01-fcb9-4a5c-ab63-a8a04ef6e2ed"}, "timezone_city_9944f4d6-06c0-484a-8a86-6fe32eefce7d": {"data": "Western European Summer Time", "locale": null, "channel": null, "attribute": "timezone_city_9944f4d6-06c0-484a-8a86-6fe32eefce7d"}, "description_city_491b2b80-474a-4254-a4ef-5f12ba30d6fc_en_US": {"data": "<p><strong>Want to live a charming getaway?</strong></p><p>Forget Venice and discover %s! You will fall for THE trendy and romantic city trip ... %s will charm you with its history, architecture, culture and people. Be enchanted by the soul of %s, strolling through the steep streets with colorful facades of Alfama or Bairro Alto. Admire the sublime views of the Tagus and the Atlantic Ocean from the viewpoints accessible by yellow tram ... There is so much to discover in %s and so little to do to escape the time of a weekend full of poetry . Do not wait any longer !</p>", "locale": "en_US", "channel": null, "attribute": "description_city_491b2b80-474a-4254-a4ef-5f12ba30d6fc"}}')
SQL;
        $cityCodes = [
            'nantes',
            'vertou',
            'niort',
            'limoge',
            'bordeaux',
            'toulouse',
            'dijon',
            'digouin',
            'puy_en_velay',
            'marseille',
            'olonne_sur_mer',
            'montaigu',
            'lanion',
            'rennes',
        ];
        $code = $cityCodes[array_rand($cityCodes)];
        $label = str_replace('_', ' ', ucfirst($code));
        $temp = rand(5, 30);
        $speed = rand(10, 50);
        $uuid = Uuid::uuid4()->toString();
        $sprintf = sprintf(
            $fakeCity,
            $code,
            $uuid,
            $code,
            str_replace('-', '_', $uuid),
            $label,
            $label,
            $temp,
            $speed,
            $label,
            $label,
            $label,
            $label
        );

        return $sprintf;
    }

    private function loadMainColors(): void
    {
        $refEntityImage = $this->uploadImage('maincolor2')->getKey();
        $blue = $this->uploadImage('mainblue')->getKey();
        $red = $this->uploadImage('mainred')->getKey();
        $green = $this->uploadImage('maingreen')->getKey();
        $black = $this->uploadImage('mainblack')->getKey();
        $sql = <<<SQL
SET foreign_key_checks = 0;
INSERT INTO `akeneo_reference_entity_reference_entity` (`identifier`, `labels`, `image`, `attribute_as_label`, `attribute_as_image`)
VALUES ('maincolor', '{\"en_US\": \"Main Color\"}', '${refEntityImage}', 'label_maincolor_491b2b80-474a-4254-a4ef-5f12ba30d7fc', 'image_maincolor_491b2b80-474a-4254-a4ef-5f12ba30d6d')
;
INSERT INTO `akeneo_reference_entity_attribute` (
  `identifier`,
  `code`,
  `reference_entity_identifier`,
  `labels`,
  `attribute_type`,
  `attribute_order`,
  `is_required`,
  `value_per_channel`,
  `value_per_locale`,
  `additional_properties`
  )
VALUES
  ('label_maincolor_491b2b80-474a-4254-a4ef-5f12ba30d7fc','label','maincolor','{\"en_US\": \"Label\"}','text',1,0,0,1,'{\"max_length\": null, \"is_textarea\": false, \"validation_rule\": \"none\", \"regular_expression\": null, \"is_rich_text_editor\": false}'),
  ('image_maincolor_491b2b80-474a-4254-a4ef-5f12ba30d6d','image','maincolor','{\"en_US\": \"Image\"}','image',2,0,0,0,'{\"max_file_size\": \"10\", \"allowed_extensions\": [\"jpeg\", \"jpg\", \"png\"]}')
;

INSERT INTO `akeneo_reference_entity_record` (`identifier`, `code`, `reference_entity_identifier`, `value_collection`)
VALUES
	('maincolor_blue_fa538845-ef57-4588-ad2e-7c6459383970','blue','maincolor','{"image_maincolor_491b2b80-474a-4254-a4ef-5f12ba30d6d": {"data": {"size": 5396, "filePath": "${blue}", "mimeType": "image/jpeg", "extension": "jpeg", "originalFilename": "blue.jpg"}, "locale": null, "channel": null, "attribute": "image_maincolor_491b2b80-474a-4254-a4ef-5f12ba30d6d"}, "label_maincolor_491b2b80-474a-4254-a4ef-5f12ba30d7fc_en_US": {"data": "Blue", "locale": "en_US", "channel": null, "attribute":"label_maincolor_491b2b80-474a-4254-a4ef-5f12ba30d7fc"}, "label_maincolor_491b2b80-474a-4254-a4ef-5f12ba30d7fc_fr_FR": {"data": "Bleu", "locale": "fr_FR", "channel": null, "attribute":"label_maincolor_491b2b80-474a-4254-a4ef-5f12ba30d7fc"}}'),
	('maincolor_red_d103c7d1-da73-4d66-94e4-aa9adaaf1569','red','maincolor','{"image_maincolor_491b2b80-474a-4254-a4ef-5f12ba30d6d": {"data": {"size": 5396, "filePath": "${red}", "mimeType": "image/jpeg", "extension": "jpeg", "originalFilename": "red.jpg"}, "locale": null, "channel": null, "attribute": "image_maincolor_491b2b80-474a-4254-a4ef-5f12ba30d6d"}, "label_maincolor_491b2b80-474a-4254-a4ef-5f12ba30d7fc_en_US": {"data": "Red", "locale": "en_US", "channel": null, "attribute":"label_maincolor_491b2b80-474a-4254-a4ef-5f12ba30d7fc"}, "label_maincolor_491b2b80-474a-4254-a4ef-5f12ba30d7fc_fr_FR": {"data": "Rouge", "locale": "fr_FR", "channel": null, "attribute":"label_maincolor_491b2b80-474a-4254-a4ef-5f12ba30d7fc"}}'),
	('maincolor_green_173ecb1d-9440-40e3-83b8-b516720fbe23','green','maincolor','{"image_maincolor_491b2b80-474a-4254-a4ef-5f12ba30d6d": {"data": {"size": 5396, "filePath": "${green}", "mimeType": "image/jpeg", "extension": "jpeg", "originalFilename": "green.jpg"}, "locale": null, "channel": null, "attribute": "image_maincolor_491b2b80-474a-4254-a4ef-5f12ba30d6d"}, "label_maincolor_491b2b80-474a-4254-a4ef-5f12ba30d7fc_en_US": {"data": "Green", "locale": "en_US", "channel": null, "attribute":"label_maincolor_491b2b80-474a-4254-a4ef-5f12ba30d7fc"}, "label_maincolor_491b2b80-474a-4254-a4ef-5f12ba30d7fc_fr_FR": {"data": "Vert", "locale": "fr_FR", "channel": null, "attribute":"label_maincolor_491b2b80-474a-4254-a4ef-5f12ba30d7fc"}}'),
	('maincolor_black_b155efea-4577-4ba8-af2a-2a80966278f6','black','maincolor','{"image_maincolor_491b2b80-474a-4254-a4ef-5f12ba30d6d": {"data": {"size": 5396, "filePath": "${black}", "mimeType": "image/jpeg", "extension": "jpeg", "originalFilename": "black.jpg"}, "locale": null, "channel": null, "attribute": "image_maincolor_491b2b80-474a-4254-a4ef-5f12ba30d6d"}, "label_maincolor_491b2b80-474a-4254-a4ef-5f12ba30d7fc_en_US": {"data": "Black", "locale": "en_US", "channel": null, "attribute":"label_maincolor_491b2b80-474a-4254-a4ef-5f12ba30d7fc"}, "label_maincolor_491b2b80-474a-4254-a4ef-5f12ba30d7fc_fr_FR": {"data": "Noir", "locale": "fr_FR", "channel": null, "attribute":"label_maincolor_491b2b80-474a-4254-a4ef-5f12ba30d7fc"}}')
;
SET foreign_key_checks = 1;
SQL;
        $this->sqlConnection->executeUpdate($sql);
    }

    private function loadDesigners(): void
    {
        $designer = $this->uploadImage('designer')->getKey();
        $philippeStarck = $this->uploadImage('philippeStarck')->getKey();
        $ronArad = $this->uploadImage('ronArad')->getKey();
        $ronAradProducts = $this->uploadImage('ronArad_products')->getKey();
        $jamesDyson = $this->uploadImage('jd-cutout')->getKey();
        $marcNewson = $this->uploadImage('Dezeen_Marc-Newson_1')->getKey();
        $sql = <<<SQL
SET foreign_key_checks = 0;
INSERT INTO `akeneo_reference_entity_reference_entity` (`identifier`, `labels`, `image`, `attribute_as_label`, `attribute_as_image`)
VALUES ('designer','{\"en_US\": \"Designer\", \"fr_FR\": \"Concepteur\"}', '${designer}', 'label_designer_491b2b80-474a-4254-a4ef-5f12ba30d8fc', 'image_designer_491b2b80-474a-4254-a4ef-5f12ba30d6e');

INSERT INTO `akeneo_reference_entity_attribute` (
  `identifier`,
  `code`,
  `reference_entity_identifier`,
  `labels`,
  `attribute_type`,
  `attribute_order`,
  `is_required`,
  `value_per_channel`,
  `value_per_locale`,
  `additional_properties`
  )
VALUES
  ('label_designer_491b2b80-474a-4254-a4ef-5f12ba30d8fc','label','designer','{\"en_US\": \"Label\"}','text',1,0,0,1,'{\"max_length\": null, \"is_textarea\": false, \"validation_rule\": \"none\", \"regular_expression\": null, \"is_rich_text_editor\": false}'),
  ('image_designer_491b2b80-474a-4254-a4ef-5f12ba30d6e','image','designer','{\"en_US\": \"Image\"}','image',2,0,0,0,'{\"max_file_size\": \"10\", \"allowed_extensions\": [\"jpeg\", \"jpg\", \"png\"]}'),
  ('description_designer_1949c44b-f04c-46c1-9010-21a6d076f35b','description','designer','{\"en_US\": \"Description\"}','text',3,0,1,0,'{\"max_length\": null, \"is_textarea\": false, \"validation_rule\": \"none\", \"regular_expression\": null, \"is_rich_text_editor\": false}'),
  ('website_designer_5d008f13-a115-4147-8b7f-122a4f1d52d4','website','designer','{\"en_US\": \"Website\"}','text',4,0,0,0,'{\"max_length\": null, \"is_textarea\": false, \"validation_rule\": \"url\", \"regular_expression\": null, \"is_rich_text_editor\": false}'),
  ('wikipediapage_designer_8b2aa74c-cc2e-486f-a514-496800214bde','wikipediapage','designer','{\"en_US\": \"Wikipedia page\"}','text',5,0,0,1,'{\"max_length\": 5000, \"is_textarea\": true, \"validation_rule\": \"none\", \"regular_expression\": null, \"is_rich_text_editor\": true}'),
  ('resume_designer_bad11eab-769b-49a1-a3e9-5a6366cc65dc','resume','designer','{\"en_US\": \"Résumé\"}','text',6,0,1,1,'{\"max_length\": null, \"is_textarea\": false, \"validation_rule\": \"none\", \"regular_expression\": null, \"is_rich_text_editor\": false}'),
  ('coverphoto_designer_e68f7b52-dfbc-4c5b-a316-73c83fdd841a','coverphoto','designer','{\"en_US\": \"Cover photo\"}','image',7,0,0,0,'{\"max_file_size\": \"10\", \"allowed_extensions\": [\"jpeg\", \"jpg\", \"png\"]}'),
  ('nationality_designer_df90ead4-8aea-42a0-a517-5554e12631bb','nationality','designer','{\"en_US\": \"Nationality\"}','text',8,0,0,1,'{\"max_length\": null, \"is_textarea\": false, \"validation_rule\": \"none\", \"regular_expression\": null, \"is_rich_text_editor\": false}'),
  ('birthdate_designer_87939c45-1d85-4134-9579-d594fff65030','birthdate','designer','{\"en_US\": \"Birthdate\"}','text',9,0,0,0,'{\"max_length\": null, \"is_textarea\": false, \"validation_rule\": \"none\", \"regular_expression\": null, \"is_rich_text_editor\": false}')
;

INSERT INTO `akeneo_reference_entity_record` (`identifier`, `code`, `reference_entity_identifier`, `value_collection`)
VALUES
  ('designer_starck_1','starck','designer','{"image_designer_491b2b80-474a-4254-a4ef-5f12ba30d6e": {"data": {"size": 5396, "filePath": "${philippeStarck}", "mimeType": "image/jpeg", "extension": "jpeg", "originalFilename": "philippeStarck.jpg"}, "locale": null, "channel": null, "attribute": "image_designer_491b2b80-474a-4254-a4ef-5f12ba30d6e"}, "label_designer_491b2b80-474a-4254-a4ef-5f12ba30d8fc_en_US": {"data": "Philippe Starck", "locale": "en_US", "channel": null, "attribute":"label_designer_491b2b80-474a-4254-a4ef-5f12ba30d8fc"}, "birthdate_designer_87939c45-1d85-4134-9579-d594fff65030": {"data": "12", "locale": null, "channel": null, "attribute": "birthdate_designer_87939c45-1d85-4134-9579-d594fff65030"}, "nationality_designer_df90ead4-8aea-42a0-a517-5554e12631bb_en_US": {"data": "French", "locale": "en_US", "channel": null, "attribute": "nationality_designer_df90ead4-8aea-42a0-a517-5554e12631bb"}, "wikipediapage_designer_8b2aa74c-cc2e-486f-a514-496800214bde_en_US": {"data": "<iframe width=\\\"600\\\" height=\\\"400\\\" src=\\\"https://www.youtube.com/embed/6yAVvlc0_sg\\\" frameBorder=\\\"0\\\"></iframe>\\\\n<p></p>\\\\n", "locale": "en_US", "channel": null, "attribute": "wikipediapage_designer_8b2aa74c-cc2e-486f-a514-496800214bde"}}'),
  ('designer_dyson_2','dyson','designer','{"image_designer_491b2b80-474a-4254-a4ef-5f12ba30d6e": {"data": {"size": 5396, "filePath": "${jamesDyson}", "mimeType": "image/jpeg", "extension": "jpeg", "originalFilename": "jamesDyson.jpg"}, "locale": null, "channel": null, "attribute": "image_designer_491b2b80-474a-4254-a4ef-5f12ba30d6e"},"label_designer_491b2b80-474a-4254-a4ef-5f12ba30d8fc_en_US": {"data": "James Dyson", "locale": "en_US", "channel": null, "attribute":"label_designer_491b2b80-474a-4254-a4ef-5f12ba30d8fc"}}'),
  ('designer_newson_3','newson','designer','{"image_designer_491b2b80-474a-4254-a4ef-5f12ba30d6e": {"data": {"size": 5396, "filePath": "${marcNewson}", "mimeType": "image/jpeg", "extension": "jpeg", "originalFilename": "marcNewson.jpg"}, "locale": null, "channel": null, "attribute": "image_designer_491b2b80-474a-4254-a4ef-5f12ba30d6e"},"label_designer_491b2b80-474a-4254-a4ef-5f12ba30d8fc_en_US": {"data": "Marc Newson", "locale": "en_US", "channel": null, "attribute":"label_designer_491b2b80-474a-4254-a4ef-5f12ba30d8fc"}}'),
  ('designer_arad_5','arad','designer','{"image_designer_491b2b80-474a-4254-a4ef-5f12ba30d6e": {"data": {"size": 5396, "filePath": "${ronArad}", "mimeType": "image/jpeg", "extension": "jpeg", "originalFilename": "ronArad.jpg"}, "locale": null, "channel": null, "attribute": "image_designer_491b2b80-474a-4254-a4ef-5f12ba30d6e"},"label_designer_491b2b80-474a-4254-a4ef-5f12ba30d8fc_en_US": {"data": "Ron Arad", "locale": "en_US", "channel": null, "attribute":"label_designer_491b2b80-474a-4254-a4ef-5f12ba30d8fc"},"website_designer_5d008f13-a115-4147-8b7f-122a4f1d52d4": {"data": "http://www.ronarad.co.uk/home/", "locale": null, "channel": null, "attribute": "website_designer_5d008f13-a115-4147-8b7f-122a4f1d52d4"}, "birthdate_designer_87939c45-1d85-4134-9579-d594fff65030": {"data": "04/24/1951", "locale": null, "channel": null, "attribute": "birthdate_designer_87939c45-1d85-4134-9579-d594fff65030"}, "coverphoto_designer_e68f7b52-dfbc-4c5b-a316-73c83fdd841a": {"data": {"size": 5396, "filePath": "${ronAradProducts}", "mimeType": "image/jpeg", "extension": "jpeg", "originalFilename": "ronArad_products.jpg"}, "locale": null, "channel": null, "attribute": "coverphoto_designer_e68f7b52-dfbc-4c5b-a316-73c83fdd841a"}, "nationality_designer_df90ead4-8aea-42a0-a517-5554e12631bb_en_US": {"data": "Israelian", "locale": "en_US", "channel": null, "attribute": "nationality_designer_df90ead4-8aea-42a0-a517-5554e12631bb"}, "wikipediapage_designer_8b2aa74c-cc2e-486f-a514-496800214bde_en_US": {"data": "<p><span style=\\\"color: rgb(102,102,102);background-color: rgb(255,255,255);font-size: 11px;font-family: Arial, Helvetica, sans-serif;\\\">Among the most influential designers of our time, Ron Arad (Israeli, b. 1951) stands out for his daredevil curiosity about technology and materials and for the versatile nature of his work. Trained at the Jerusalem Academy of Art and at London\\'s Architectural Association, Arad has produced an outstanding array of innovative objects over the past twenty-five years, from almost unlimited series of objects to carbon fiber armchairs and polyurethane bottle racks. He has also designed memorable spaces, some plastic and tactile, others ethereal and digital. This exhibition will be the first major retrospective of Arad\\'s design work in the United States. Arad relies on the computer and its rapid manufacturing capabilities as much as he relies on the soldering apparatus in his metal workshop. His beautiful furniture can even receive and display SMS and Bluetooth messages from mobile phones and Palm Pilots. Idiosyncratic and surprising, and also very beautiful, Arad\\'s designs communicate the joy of invention, pleasure and humor, and pride in the display of their technical and constructive skills. The exhibition will open in Paris in the fall of 2009.</span></p>\\\\n", "locale": "en_US", "channel": null, "attribute": "wikipediapage_designer_8b2aa74c-cc2e-486f-a514-496800214bde"}}')
;
SET foreign_key_checks = 1;
SQL;
        $this->sqlConnection->executeUpdate($sql);
    }

    private function loadBrands(): void
    {
        $brand = $this->uploadImage('brand')->getKey();
        $alessi = $this->uploadImage('Alessi')->getKey();
        $alessiProducts = $this->uploadImage('alessi_products')->getKey();
        $bangolufsen = $this->uploadImage('B&O')->getKey();
        $bangolufsenProducts = $this->uploadImage('b&o_products')->getKey();
        $fatboy = $this->uploadImage('Fatboy')->getKey();
        $fatboyProducts = $this->uploadImage('fatboy_products')->getKey();
        $fermob = $this->uploadImage('Fermob')->getKey();
        $fermobProducts = $this->uploadImage('fermob_products')->getKey();
        $kartell = $this->uploadImage('Kartell')->getKey();
        $kartellProducts = $this->uploadImage('kartell_products')->getKey();
        $lexon = $this->uploadImage('Lexon')->getKey();
        $lexonProducts = $this->uploadImage('lexon_products')->getKey();
        $muuto = $this->uploadImage('Muuto')->getKey();
        $muutoProducts = $this->uploadImage('muuto_products')->getKey();
        $tomdixon = $this->uploadImage('TomDixon')->getKey();
        $tomdixonProducts = $this->uploadImage('tom-dixon_products')->getKey();
        $sql = <<<SQL
SET foreign_key_checks = 0;
INSERT INTO `akeneo_reference_entity_reference_entity` (`identifier`, `labels`, `image`, `attribute_as_label`, `attribute_as_image`)
VALUES ('brand','{\"en_US\": \"Brand\", \"fr_FR\": \"Marque\"}', '${brand}', 'label_brand_491b2b80-474a-4254-a4ef-5f12ba10d8fc', 'image_brand_491b2b80-474a-4254-a4ef-5f12ba30d6d');
INSERT INTO `akeneo_reference_entity_attribute` (
  `identifier`,
  `code`,
  `reference_entity_identifier`,
  `labels`,
  `attribute_type`,
  `attribute_order`,
  `is_required`,
  `value_per_channel`,
  `value_per_locale`,
  `additional_properties`
  )
VALUES
  ('label_brand_491b2b80-474a-4254-a4ef-5f12ba10d8fc','label','brand','{\"en_US\": \"Label\"}','text',1,0,0,1,'{\"max_length\": null, \"is_textarea\": false, \"validation_rule\": \"none\", \"regular_expression\": null, \"is_rich_text_editor\": false}'),
  ('image_brand_491b2b80-474a-4254-a4ef-5f12ba30d6d','image','brand','{\"en_US\": \"Image\"}','image',2,0,0,0,'{\"max_file_size\": \"10\", \"allowed_extensions\": [\"jpeg\", \"jpg\", \"png\"]}'),
  ('founded_brand_fff5387e-64ce-4228-b68e-af8704867761','founded','brand','{\"en_US\": \"Founded\", \"fr_FR\": \"Fondé\"}','text',3,0,0,0,'{\"max_length\": null, \"is_textarea\": false, \"validation_rule\": \"none\", \"regular_expression\": null, \"is_rich_text_editor\": false}'),
  ('nationality_brand_f60def21-f44f-449d-9fae-7a4f76cded60','nationality','brand','{\"en_US\": \"Country\", \"fr_FR\": \"Pays\"}','text',4,0,0,0,'{\"max_length\": null, \"is_textarea\": false, \"validation_rule\": \"none\", \"regular_expression\": null, \"is_rich_text_editor\": false}'),
  ('photo_brand_8587cda6-58c8-47fa-9278-033e1d8c735c','photo','brand','{\"en_US\": \"Photo\", \"fr_FR\": \"Photo\"}','image',5,0,0,0,'{\"max_file_size\": null, \"allowed_extensions\": []}'),
  ('description_brand_d794e371-5d9b-4c4b-918b-9714a9b65a71','description','brand','{\"en_US\": \"Description\", \"fr_FR\": \"Description\"}','text',6,1,1,1,'{\"max_length\": null, \"is_textarea\": true, \"validation_rule\": \"none\", \"regular_expression\": null, \"is_rich_text_editor\": true}'),
  ('founder_brand_eb505477-3139-4c6d-9014-ac8091c2c2de','founder','brand','{\"en_US\": \"Designer\", \"fr_FR\": \"Designer\"}','text',7,1,0,0,'{\"max_length\": null, \"is_textarea\": false, \"validation_rule\": \"none\", \"regular_expression\": null, \"is_rich_text_editor\": false}')
;

INSERT INTO `akeneo_reference_entity_record` (`identifier`, `code`, `reference_entity_identifier`, `value_collection`)
VALUES
  ('brand_alessi_dc1c552a-108c-4e1d-9d72-7f17368bdb5a','alessi','brand','{"image_brand_491b2b80-474a-4254-a4ef-5f12ba30d6d": {"data": {"size": 5396, "filePath": "${alessi}", "mimeType": "image/jpeg", "extension": "jpeg", "originalFilename": "alessi.jpg"}, "locale": null, "channel": null, "attribute": "image_brand_491b2b80-474a-4254-a4ef-5f12ba30d6d"},"label_brand_491b2b80-474a-4254-a4ef-5f12ba10d8fc_en_US": {"data": "Alessi", "locale": "en_US", "channel": null, "attribute":"label_brand_491b2b80-474a-4254-a4ef-5f12ba10d8fc"}, "label_brand_491b2b80-474a-4254-a4ef-5f12ba10d8fc_fr_FR": {"data": "Alessi", "locale": "fr_FR", "channel": null, "attribute":"label_brand_491b2b80-474a-4254-a4ef-5f12ba10d8fc"}, "photo_brand_8587cda6-58c8-47fa-9278-033e1d8c735c": {"data": {"size": 168107, "filePath": "${alessiProducts}", "mimeType": "image/jpeg", "extension": "jpg", "originalFilename": "alessi_products.jpg"}, "locale": null, "channel": null, "attribute": "photo_brand_8587cda6-58c8-47fa-9278-033e1d8c735c"}, "founded_brand_fff5387e-64ce-4228-b68e-af8704867761": {"data": "1921", "locale": null, "channel": null, "attribute": "founded_brand_fff5387e-64ce-4228-b68e-af8704867761"}, "founder_brand_eb505477-3139-4c6d-9014-ac8091c2c2de": {"data": "Marcel Wanders", "locale": null, "channel": null, "attribute": "founder_brand_eb505477-3139-4c6d-9014-ac8091c2c2de"}, "nationality_brand_f60def21-f44f-449d-9fae-7a4f76cded60": {"data": "Italy", "locale": null, "channel": null, "attribute": "nationality_brand_f60def21-f44f-449d-9fae-7a4f76cded60"}, "description_brand_d794e371-5d9b-4c4b-918b-9714a9b65a71_ecommerce_en_US": {"data": "<p><span style=\\\"color: rgb(102,102,102);background-color: rgb(255,255,255);font-size: 11px;font-family: Arial, Helvetica, sans-serif;\\\">Alessi is truly a \\\"dream factory\\\"! This famous Italian brand has been enhancing our daily lives for more than 80 years thanks to its beautiful and functional items which are designed by leading architects and designers. At Alessi, design has been a family affair since 1921. Initially focusing on coffee services and trays, Alessi acquired international popularity during the 1950s through working with renowned architects and designers such as Ettore Sottsass.</span>&nbsp;</p>\\\\n", "locale": "en_US", "channel": "ecommerce", "attribute": "description_brand_d794e371-5d9b-4c4b-918b-9714a9b65a71"}, "description_brand_d794e371-5d9b-4c4b-918b-9714a9b65a71_ecommerce_fr_FR": {"data": "<p><strong>A</strong><span style=\\\"color: rgb(102,102,102);background-color: rgb(255,255,255);font-size: 11px;font-family: Arial, Helvetica, sans-serif;\\\">lessi est une véritable \\\"Usine à rêves\\\" ! Cette célèbre marque italienne sublime notre quotidien depuis plus de 80 ans avec des objets beaux et fonctionnels, dessinés par les plus grands architectes et designers. Chez Alessi, le design, c\\'est une histoire de famille depuis 1921. Se concentrant au départ sur les services à café et les plateaux, Alessi acquiert dès les années 1950 une popularité internationale en collaborant  avec des architectes et designers de renom tel que</span><a href=\\\"https://www.madeindesign.com/d-ettore-sottsass.html\\\" target=\\\"_self\\\"> <span style=\\\"color: rgb(102,102,102);background-color: rgb(255,255,255);font-size: 11px;font-family: Arial, Helvetica, sans-serif;\\\">Ettore Sottsass</span></a></p>\\\\n", "locale": "fr_FR", "channel": "ecommerce", "attribute": "description_brand_d794e371-5d9b-4c4b-918b-9714a9b65a71"}}'),
  ('brand_bangolufsen_bea84235-fd91-423b-8058-ff9e2dd1490a','bangolufsen','brand','{"image_brand_491b2b80-474a-4254-a4ef-5f12ba30d6d": {"data": {"size": 5396, "filePath": "${bangolufsen}", "mimeType": "image/jpeg", "extension": "jpeg", "originalFilename": "bangolufsen.jpg"}, "locale": null, "channel": null, "attribute": "image_brand_491b2b80-474a-4254-a4ef-5f12ba30d6d"},"label_brand_491b2b80-474a-4254-a4ef-5f12ba10d8fc_en_US": {"data": "Bang & Olufsen", "locale": "en_US", "channel": null, "attribute":"label_brand_491b2b80-474a-4254-a4ef-5f12ba10d8fc"}, "label_brand_491b2b80-474a-4254-a4ef-5f12ba10d8fc_fr_FR": {"data": "Bang & Olufsen", "locale": "fr_FR", "channel": null, "attribute":"label_brand_491b2b80-474a-4254-a4ef-5f12ba10d8fc"}, "photo_brand_8587cda6-58c8-47fa-9278-033e1d8c735c": {"data": {"size": 32580, "filePath": "${bangolufsenProducts}", "mimeType": "image/jpeg", "extension": "jpg", "originalFilename": "b&o_products.jpg"}, "locale": null, "channel": null, "attribute": "photo_brand_8587cda6-58c8-47fa-9278-033e1d8c735c"}, "founded_brand_fff5387e-64ce-4228-b68e-af8704867761": {"data": "1925", "locale": null, "channel": null, "attribute": "founded_brand_fff5387e-64ce-4228-b68e-af8704867761"}, "founder_brand_eb505477-3139-4c6d-9014-ac8091c2c2de": {"data": "Cecilie Manz", "locale": null, "channel": null, "attribute": "founder_brand_eb505477-3139-4c6d-9014-ac8091c2c2de"}, "nationality_brand_f60def21-f44f-449d-9fae-7a4f76cded60": {"data": "Denmark", "locale": null, "channel": null, "attribute": "nationality_brand_f60def21-f44f-449d-9fae-7a4f76cded60"}, "description_brand_d794e371-5d9b-4c4b-918b-9714a9b65a71_ecommerce_en_US": {"data": "<p style=\\\"text-align:justify;\\\"><span style=\\\"color: rgb(9,30,66);background-color: rgb(255,255,255);font-size: 14px;font-family: -apple-system, system-ui, \\\"Segoe UI\\\", Roboto, \\\"Noto Sans\\\", Ubuntu, \\\"Droid Sans\\\", \\\"Helvetica Neue\\\", sans-serif;\\\">B&amp;O PLAY delivers stand-alone products with clear and simple operations - portable products that are intuitive to use, easy to integrate into your daily life, and deliver excellent high-quality experiences.</span></p>\\\\n<p style=\\\"text-align:justify;\\\"><span style=\\\"color: rgb(9,30,66);background-color: rgb(255,255,255);font-size: 14px;font-family: -apple-system, system-ui, \\\"Segoe UI\\\", Roboto, \\\"Noto Sans\\\", Ubuntu, \\\"Droid Sans\\\", \\\"Helvetica Neue\\\", sans-serif;\\\"> ‘’We want to evoke senses, to elevate the experience of listening and watching. We have spoken to musicians and studio recorders who all love the fact that more people listen to music in more places, but hate the fact that the quality of the listening experience has been eroded. We want to provide the opportunity to experience media in a convenient and easy way but still in outstanding high quality.  Firmly grounded in our 88-year history in Bang &amp; Olufsen, we interpret the same core values for a new type of contemporary products.\\\"</span></p>\\\\n", "locale": "en_US", "channel": "ecommerce", "attribute": "description_brand_d794e371-5d9b-4c4b-918b-9714a9b65a71"}}'),
  ('brand_fatboy_a00d1caa-7d86-412d-9263-2b3410f31029','fatboy','brand','{"image_brand_491b2b80-474a-4254-a4ef-5f12ba30d6d": {"data": {"size": 5396, "filePath": "${fatboy}", "mimeType": "image/jpeg", "extension": "jpeg", "originalFilename": "fatboy.jpg"}, "locale": null, "channel": null, "attribute": "image_brand_491b2b80-474a-4254-a4ef-5f12ba30d6d"},"label_brand_491b2b80-474a-4254-a4ef-5f12ba10d8fc_en_US": {"data": "Fatboy", "locale": "en_US", "channel": null, "attribute":"label_brand_491b2b80-474a-4254-a4ef-5f12ba10d8fc"}, "label_brand_491b2b80-474a-4254-a4ef-5f12ba10d8fc_fr_FR": {"data": "Fatboy", "locale": "fr_FR", "channel": null, "attribute":"label_brand_491b2b80-474a-4254-a4ef-5f12ba10d8fc"}, "photo_brand_8587cda6-58c8-47fa-9278-033e1d8c735c": {"data": {"size": 176910, "filePath": "${fatboyProducts}", "mimeType": "image/jpeg", "extension": "jpg", "originalFilename": "fatboy_products.jpg"}, "locale": null, "channel": null, "attribute": "photo_brand_8587cda6-58c8-47fa-9278-033e1d8c735c"}, "founded_brand_fff5387e-64ce-4228-b68e-af8704867761": {"data": "1998", "locale": null, "channel": null, "attribute": "founded_brand_fff5387e-64ce-4228-b68e-af8704867761"}, "founder_brand_eb505477-3139-4c6d-9014-ac8091c2c2de": {"data": "Alex Bergman", "locale": null, "channel": null, "attribute": "founder_brand_eb505477-3139-4c6d-9014-ac8091c2c2de"}, "nationality_brand_f60def21-f44f-449d-9fae-7a4f76cded60": {"data": "Netherlands", "locale": null, "channel": null, "attribute": "nationality_brand_f60def21-f44f-449d-9fae-7a4f76cded60"}, "description_brand_d794e371-5d9b-4c4b-918b-9714a9b65a71_ecommerce_en_US": {"data": "<p><span style=\\\"color: rgb(102,102,102);background-color: rgb(255,255,255);font-size: 11px;font-family: Arial, Helvetica, sans-serif;\\\">These 21st Century beanbags combine high quality with exclusive cheekiness. Inspired by the music of Fatboy Slim, the Finnish designer, Jukka Setalla appeared to be on the cutting edge of design. But it was Dutchman, Alex Bergman who revolutionized the product in 2002 by creating a brand around it, proving that the Fatboy could indeed sell itself. A strategy of the over-sized life, the Fatboy family tree includes gigantic lamps, magical hammocks, and umbrellas, bringing about the ‘wonder-fuller life’.</span></p>\\\\n", "locale": "en_US", "channel": "ecommerce", "attribute": "description_brand_d794e371-5d9b-4c4b-918b-9714a9b65a71"}}'),
  ('brand_fermob_57362b29-d213-4656-aec2-875a182e3561','fermob','brand','{"image_brand_491b2b80-474a-4254-a4ef-5f12ba30d6d": {"data": {"size": 5396, "filePath": "${fermob}", "mimeType": "image/jpeg", "extension": "jpeg", "originalFilename": "fermob.jpg"}, "locale": null, "channel": null, "attribute": "image_brand_491b2b80-474a-4254-a4ef-5f12ba30d6d"},"label_brand_491b2b80-474a-4254-a4ef-5f12ba10d8fc_en_US": {"data": "Fermob", "locale": "en_US", "channel": null, "attribute":"label_brand_491b2b80-474a-4254-a4ef-5f12ba10d8fc"}, "label_brand_491b2b80-474a-4254-a4ef-5f12ba10d8fc_fr_FR": {"data": "Fermob", "locale": "fr_FR", "channel": null, "attribute":"label_brand_491b2b80-474a-4254-a4ef-5f12ba10d8fc"}, "photo_brand_8587cda6-58c8-47fa-9278-033e1d8c735c": {"data": {"size": 162758, "filePath": "${fermobProducts}", "mimeType": "image/jpeg", "extension": "jpg", "originalFilename": "fermob_products.jpg"}, "locale": null, "channel": null, "attribute": "photo_brand_8587cda6-58c8-47fa-9278-033e1d8c735c"}, "founded_brand_fff5387e-64ce-4228-b68e-af8704867761": {"data": "1989", "locale": null, "channel": null, "attribute": "founded_brand_fff5387e-64ce-4228-b68e-af8704867761"}, "founder_brand_eb505477-3139-4c6d-9014-ac8091c2c2de": {"data": "Pascal Mourgue", "locale": null, "channel": null, "attribute": "founder_brand_eb505477-3139-4c6d-9014-ac8091c2c2de"}, "nationality_brand_f60def21-f44f-449d-9fae-7a4f76cded60": {"data": "France", "locale": null, "channel": null, "attribute": "nationality_brand_f60def21-f44f-449d-9fae-7a4f76cded60"}, "description_brand_d794e371-5d9b-4c4b-918b-9714a9b65a71_ecommerce_en_US": {"data": "<p><span style=\\\"color: rgb(102,102,102);background-color: rgb(255,255,255);font-size: 11px;font-family: Arial, Helvetica, sans-serif;\\\">A French outdoor furniture brand that breathes creative spirit into the design process, Fermob has been working with established and up &amp; coming designers from all walks of life for over 20 years. From Fermob’s factory in Thoissey, discover how technology and process are combined to produce high-quality furniture that is definitively made in France. Fermob’s commitment to the environment, know-how &amp; production is revealed in furniture inspired by places and events, like the Luxembourg or Biarritz series as well as the 60s or 1900 chair, which highlight both the elegance and refinement of a philosophy just waiting to be discovered.</span></p>\\\\n", "locale": "en_US", "channel": "ecommerce", "attribute": "description_brand_d794e371-5d9b-4c4b-918b-9714a9b65a71"}}'),
  ('brand_kartell_79505e53-9694-47e1-aa5d-d8812c5ed699','kartell','brand', '{"image_brand_491b2b80-474a-4254-a4ef-5f12ba30d6d": {"data": {"size": 5396, "filePath": "${kartell}", "mimeType": "image/jpeg", "extension": "jpeg", "originalFilename": "kartell.jpg"}, "locale": null, "channel": null, "attribute": "image_brand_491b2b80-474a-4254-a4ef-5f12ba30d6d"},"label_brand_491b2b80-474a-4254-a4ef-5f12ba10d8fc_en_US": {"data": "Kartell", "locale": "en_US", "channel": null, "attribute":"label_brand_491b2b80-474a-4254-a4ef-5f12ba10d8fc"}, "label_brand_491b2b80-474a-4254-a4ef-5f12ba10d8fc_fr_FR": {"data": "Kartell", "locale": "fr_FR", "channel": null, "attribute":"label_brand_491b2b80-474a-4254-a4ef-5f12ba10d8fc"}, "photo_brand_8587cda6-58c8-47fa-9278-033e1d8c735c": {"data": {"size": 88980, "filePath": "${kartellProducts}", "mimeType": "image/jpeg", "extension": "jpg", "originalFilename": "kartell_products.jpg"}, "locale": null, "channel": null, "attribute": "photo_brand_8587cda6-58c8-47fa-9278-033e1d8c735c"}, "founded_brand_fff5387e-64ce-4228-b68e-af8704867761": {"data": "1949", "locale": null, "channel": null, "attribute": "founded_brand_fff5387e-64ce-4228-b68e-af8704867761"}, "founder_brand_eb505477-3139-4c6d-9014-ac8091c2c2de": {"data": "Philippe Starck", "locale": null, "channel": null, "attribute": "founder_brand_eb505477-3139-4c6d-9014-ac8091c2c2de"}, "nationality_brand_f60def21-f44f-449d-9fae-7a4f76cded60": {"data": "Italy", "locale": null, "channel": null, "attribute": "nationality_brand_f60def21-f44f-449d-9fae-7a4f76cded60"}, "description_brand_d794e371-5d9b-4c4b-918b-9714a9b65a71_ecommerce_en_US": {"data": "<p><span style=\\\"color: rgb(102,102,102);background-color: rgb(255,255,255);font-size: 11px;font-family: Arial, Helvetica, sans-serif;\\\">‘’Kartell - The Culture of Plastics’’… In just over 50 years, this famous Italian company has revolutionised plastic, elevating it and propelling it into the refined world of luxury. Today, Kartell has more than a hundred showrooms all over the world and a good number of its creations have become cult pieces on display in the most prestigious museums. The famous Kartell Louis Ghost armchair has the most sales for armchairs in the world, with 1.5 million sales! Challenging the material, constantly researching new tactile, visual and aesthetic effects - Kartell faces every challenge!</span>&nbsp;</p>\\\\n", "locale": "en_US", "channel": "ecommerce", "attribute": "description_brand_d794e371-5d9b-4c4b-918b-9714a9b65a71"}, "description_brand_d794e371-5d9b-4c4b-918b-9714a9b65a71_ecommerce_fr_FR": {"data": "<p style=\\\"text-align:justify;\\\"><span style=\\\"color: rgb(102,102,102);background-color: rgb(255,255,255);font-size: 11px;font-family: Arial, Helvetica, sans-serif;\\\">Entreprise leader du design, fondée à Milan en 1949 par Giulio Castelli, Kartell est depuis près de 70 ans une des entreprises symbole de la conception Made in Italy. Une histoire pleine de succès racontée à travers un incroyable éventail de produits - meubles, accessoires de décoration, éclairage - devenus partie intégrante du paysage domestique, et même de véritables icônes du design contemporain.</span></p>\\\\n<p style=\\\"text-align:justify;\\\"><span style=\\\"color: rgb(102,102,102);background-color: rgb(255,255,255);font-size: 11px;font-family: Arial, Helvetica, sans-serif;\\\">Depuis 1988, Claudio Luti, l’héritier de l’«esprit Kartell», insuffle une prodigieuse dynamique à la marque.</span>&nbsp;</p>\\\\n", "locale": "fr_FR", "channel": "ecommerce", "attribute": "description_brand_d794e371-5d9b-4c4b-918b-9714a9b65a71"}}'),
  ('brand_lexon_4c3e8f80-dda4-456e-862e-59dc6e2e02f6','lexon','brand','{"image_brand_491b2b80-474a-4254-a4ef-5f12ba30d6d": {"data": {"size": 5396, "filePath": "${lexon}", "mimeType": "image/jpeg", "extension": "jpeg", "originalFilename": "lexon.jpg"}, "locale": null, "channel": null, "attribute": "image_brand_491b2b80-474a-4254-a4ef-5f12ba30d6d"},"label_brand_491b2b80-474a-4254-a4ef-5f12ba10d8fc_en_US": {"data": "Lexon", "locale": "en_US", "channel": null, "attribute":"label_brand_491b2b80-474a-4254-a4ef-5f12ba10d8fc"}, "label_brand_491b2b80-474a-4254-a4ef-5f12ba10d8fc_fr_FR": {"data": "Lexon", "locale": "fr_FR", "channel": null, "attribute":"label_brand_491b2b80-474a-4254-a4ef-5f12ba10d8fc"}, "photo_brand_8587cda6-58c8-47fa-9278-033e1d8c735c": {"data": {"size": 34100, "filePath": "${lexonProducts}", "mimeType": "image/jpeg", "extension": "jpg", "originalFilename": "lexon_products.jpg"}, "locale": null, "channel": null, "attribute": "photo_brand_8587cda6-58c8-47fa-9278-033e1d8c735c"}, "founded_brand_fff5387e-64ce-4228-b68e-af8704867761": {"data": "1991", "locale": null, "channel": null, "attribute": "founded_brand_fff5387e-64ce-4228-b68e-af8704867761"}, "founder_brand_eb505477-3139-4c6d-9014-ac8091c2c2de": {"data": "Marc Berthier", "locale": null, "channel": null, "attribute": "founder_brand_eb505477-3139-4c6d-9014-ac8091c2c2de"}, "nationality_brand_f60def21-f44f-449d-9fae-7a4f76cded60": {"data": "France", "locale": null, "channel": null, "attribute": "nationality_brand_f60def21-f44f-449d-9fae-7a4f76cded60"}, "description_brand_d794e371-5d9b-4c4b-918b-9714a9b65a71_ecommerce_en_US": {"data": "<p><span style=\\\"color: rgb(102,102,102);background-color: rgb(255,255,255);font-size: 11px;font-family: Arial, Helvetica, sans-serif;\\\">For over 20 years, Lexon has been pioneering creative solutions in the world of objects and communication by balancing a unique artistic and industrial equation. Innovative, functional, well designed and manufactured, their products are both timeless and functional. Based on a simple philosophy: to delight and be useful. Lexon products start with imagination, are realised through effective design and find their way to enhance your desk and workspace.</span></p>\\\\n", "locale": "en_US", "channel": "ecommerce", "attribute": "description_brand_d794e371-5d9b-4c4b-918b-9714a9b65a71"}}'),
  ('brand_muuto_00fb9223-8636-4707-aa43-9058acfdfbe4','muuto','brand','{"image_brand_491b2b80-474a-4254-a4ef-5f12ba30d6d": {"data": {"size": 5396, "filePath": "${muuto}", "mimeType": "image/jpeg", "extension": "jpeg", "originalFilename": "muuto.jpg"}, "locale": null, "channel": null, "attribute": "image_brand_491b2b80-474a-4254-a4ef-5f12ba30d6d"},"label_brand_491b2b80-474a-4254-a4ef-5f12ba10d8fc_en_US": {"data": "Muuto", "locale": "en_US", "channel": null, "attribute":"label_brand_491b2b80-474a-4254-a4ef-5f12ba10d8fc"}, "label_brand_491b2b80-474a-4254-a4ef-5f12ba10d8fc_fr_FR": {"data": "Muuto", "locale": "fr_FR", "channel": null, "attribute":"label_brand_491b2b80-474a-4254-a4ef-5f12ba10d8fc"}, "photo_brand_8587cda6-58c8-47fa-9278-033e1d8c735c": {"data": {"size": 118854, "filePath": "${muutoProducts}", "mimeType": "image/jpeg", "extension": "jpg", "originalFilename": "muuto_products.jpg"}, "locale": null, "channel": null, "attribute": "photo_brand_8587cda6-58c8-47fa-9278-033e1d8c735c"}, "founded_brand_fff5387e-64ce-4228-b68e-af8704867761": {"data": "2008", "locale": null, "channel": null, "attribute": "founded_brand_fff5387e-64ce-4228-b68e-af8704867761"}, "founder_brand_eb505477-3139-4c6d-9014-ac8091c2c2de": {"data": "Anderssen & Voll", "locale": null, "channel": null, "attribute": "founder_brand_eb505477-3139-4c6d-9014-ac8091c2c2de"}, "nationality_brand_f60def21-f44f-449d-9fae-7a4f76cded60": {"data": "Denmark", "locale": null, "channel": null, "attribute": "nationality_brand_f60def21-f44f-449d-9fae-7a4f76cded60"}, "description_brand_d794e371-5d9b-4c4b-918b-9714a9b65a71_ecommerce_en_US": {"data": "<p><span style=\\\"color: rgb(102,102,102);background-color: rgb(255,255,255);font-size: 11px;font-family: Arial, Helvetica, sans-serif;\\\">Founded in 2008, this young Danish manufacturing enterprise represents the new wave in Scandinavian design. There are two young designers behind Muuto, who have set out to develop traditional Scandinavian design in a new and original context. To achieve this, they have called on the creativity of a new generation of Nordic designers such as Louise Campbell, From Us With Love, Norway Says and Ole Jensen. Muuto takes the very best of Scandinavian talent and gives them total freedom to express their personal vision for an object in daily life. The result: a simple salt shaker becomes a work of art, stacked cylinders form a bookcase and a vase takes the form of cups balanced on top of one...</span></p>\\\\n", "locale": "en_US", "channel": "ecommerce", "attribute": "description_brand_d794e371-5d9b-4c4b-918b-9714a9b65a71"}}'),
  ('brand_tomdixon_076948af-6b73-4844-80f3-1a033998874b','tomdixon','brand','{"image_brand_491b2b80-474a-4254-a4ef-5f12ba30d6d": {"data": {"size": 5396, "filePath": "${tomdixon}", "mimeType": "image/jpeg", "extension": "jpeg", "originalFilename": "tomdixon.jpg"}, "locale": null, "channel": null, "attribute": "image_brand_491b2b80-474a-4254-a4ef-5f12ba30d6d"},"label_brand_491b2b80-474a-4254-a4ef-5f12ba10d8fc_en_US": {"data": "Tom Dixon", "locale": "en_US", "channel": null, "attribute":"label_brand_491b2b80-474a-4254-a4ef-5f12ba10d8fc"}, "label_brand_491b2b80-474a-4254-a4ef-5f12ba10d8fc_fr_FR": {"data": "Tom Dixon", "locale": "fr_FR", "channel": null, "attribute":"label_brand_491b2b80-474a-4254-a4ef-5f12ba10d8fc"}, "photo_brand_8587cda6-58c8-47fa-9278-033e1d8c735c": {"data": {"size": 29189, "filePath": "${tomdixonProducts}", "mimeType": "image/jpeg", "extension": "jpg", "originalFilename": "tom-dixon_products.jpg"}, "locale": null, "channel": null, "attribute": "photo_brand_8587cda6-58c8-47fa-9278-033e1d8c735c"}, "founded_brand_fff5387e-64ce-4228-b68e-af8704867761": {"data": "2002", "locale": null, "channel": null, "attribute": "founded_brand_fff5387e-64ce-4228-b68e-af8704867761"}, "founder_brand_eb505477-3139-4c6d-9014-ac8091c2c2de": {"data": "Tom Dixon", "locale": null, "channel": null, "attribute": "founder_brand_eb505477-3139-4c6d-9014-ac8091c2c2de"}, "nationality_brand_f60def21-f44f-449d-9fae-7a4f76cded60": {"data": "United Kingdom", "locale": null, "channel": null, "attribute": "nationality_brand_f60def21-f44f-449d-9fae-7a4f76cded60"}, "description_brand_d794e371-5d9b-4c4b-918b-9714a9b65a71_ecommerce_en_US": {"data": "<p style=\\\"text-align:justify;\\\"></p>\\\\n<p><span style=\\\"color: rgb(102,102,102);background-color: rgb(255,255,255);font-size: 11px;font-family: Arial, Helvetica, sans-serif;\\\">Tom Dixon is one of the most original British designers of his generation; and a rebel of the Avant Garde of international design! Awarded the title \\\"Designer of the Year\\\" in 2006, with exhibits in museums throughout the whole world, this self-taught designer has had a truly unusual career. In 1980 Dixon was a rock guitarist, working as a DJ in a club at night, and by day learning how to solder with a friend who ran a garage. He started his career by making furniture from recycled metal. Twenty years later, this self-made man is famous throughout the whole world. After having been artistic director at Habitat, he set up his own firm to make his collections of lighting...</span><br></p>\\\\n", "locale": "en_US", "channel": "ecommerce", "attribute": "description_brand_d794e371-5d9b-4c4b-918b-9714a9b65a71"}}')
  ;
SET foreign_key_checks = 1;
SQL;
        $this->sqlConnection->executeUpdate($sql);
    }

    public function loadColors(): void
    {
        $color = $this->uploadImage('color')->getKey();
        $bleuMarine = $this->uploadImage('bleumarine')->getKey();
        $rougeCoquelicot = $this->uploadImage('rougecoquelicot')->getKey();
        $bleuTempete = $this->uploadImage('bleutempete')->getKey();
        $rougePiment = $this->uploadImage('rougepiment')->getKey();
        $sql = <<< SQL
SET foreign_key_checks = 0;
INSERT INTO `akeneo_reference_entity_reference_entity` (`identifier`, `labels`, `image`, `attribute_as_label`, `attribute_as_image`)
VALUES ('color','{\"en_US\": \"Color\"}', '${color}', 'label_color_491b2b80-474a-4254-a4ef-5f19ba10d8fc', 'image_color_491b2b80-474a-4254-a4ef-5f12ba30d6d');

INSERT INTO `akeneo_reference_entity_attribute` (
  `identifier`,
  `code`,
  `reference_entity_identifier`,
  `labels`,
  `attribute_type`,
  `attribute_order`,
  `is_required`,
  `value_per_channel`,
  `value_per_locale`,
  `additional_properties`
  )
VALUES
  ('label_color_491b2b80-474a-4254-a4ef-5f19ba10d8fc','label','color','{\"en_US\": \"Label\"}','text',1,0,0,1,'{\"max_length\": null, \"is_textarea\": false, \"validation_rule\": \"none\", \"regular_expression\": null, \"is_rich_text_editor\": false}'),
  ('image_color_491b2b80-474a-4254-a4ef-5f12ba30d6d','image','color','{\"en_US\": \"Image\"}','image',2,0,0,0,'{\"max_file_size\": \"10\", \"allowed_extensions\": [\"jpeg\", \"jpg\", \"png\"]}'),
  ('description_color_55f3847b-d5d7-4f33-bf14-a219ac6f29e4','description','color','{\"en_US\": \"description\"}','text',3,0,0,0,'{\"max_length\": null, \"is_textarea\": false, \"validation_rule\": \"none\", \"regular_expression\": null, \"is_rich_text_editor\": false}'),
  ('maincolor_color_e27aed6f-6db2-4cbf-ad64-d7a008a3c0ad','maincolor','color','{\"en_US\": \"Main color\"}','text',4,0,0,0,'{\"max_length\": null, \"is_textarea\": false, \"validation_rule\": \"none\", \"regular_expression\": null, \"is_rich_text_editor\": false}')
;

INSERT INTO `akeneo_reference_entity_record` (`identifier`, `code`, `reference_entity_identifier`, `value_collection`)
VALUES
	('color_navyblue_6d3ba943-6353-4d85-970d-2210f5373c2d','navyblue','color','{"image_color_491b2b80-474a-4254-a4ef-5f12ba30d6d": {"data": {"size": 5396, "filePath": "${bleuMarine}", "mimeType": "image/jpeg", "extension": "jpeg", "originalFilename": "bleuMarine.jpg"}, "locale": null, "channel": null, "attribute": "image_color_491b2b80-474a-4254-a4ef-5f12ba30d6d"},"label_color_491b2b80-474a-4254-a4ef-5f19ba10d8fc_en_US": {"data": "Navy blue", "locale": "en_US", "channel": null, "attribute":"label_color_491b2b80-474a-4254-a4ef-5f19ba10d8fc"}, "label_color_491b2b80-474a-4254-a4ef-5f19ba10d8fc_fr_FR": {"data": "Bleu marine", "locale": "fr_FR", "channel": null, "attribute":"label_color_491b2b80-474a-4254-a4ef-5f19ba10d8fc"},"maincolor_color_e27aed6f-6db2-4cbf-ad64-d7a008a3c0ad": {"data": "Blue", "locale": null, "channel": null, "attribute": "maincolor_color_e27aed6f-6db2-4cbf-ad64-d7a008a3c0ad"}}'),
	('color_redpoppy_8f383814-8fbd-4ce7-bed3-451fa20eeb26','redpoppy','color','{"image_color_491b2b80-474a-4254-a4ef-5f12ba30d6d": {"data": {"size": 5396, "filePath": "${rougeCoquelicot}", "mimeType": "image/jpeg", "extension": "jpeg", "originalFilename": "rougeCoquelicot.jpg"}, "locale": null, "channel": null, "attribute": "image_color_491b2b80-474a-4254-a4ef-5f12ba30d6d"},"label_color_491b2b80-474a-4254-a4ef-5f19ba10d8fc_en_US": {"data": "Red poppy", "locale": "en_US", "channel": null, "attribute":"label_color_491b2b80-474a-4254-a4ef-5f19ba10d8fc"}, "label_color_491b2b80-474a-4254-a4ef-5f19ba10d8fc_fr_FR": {"data": "Rouge coquelicot", "locale": "fr_FR", "channel": null, "attribute":"label_color_491b2b80-474a-4254-a4ef-5f19ba10d8fc"},"maincolor_color_e27aed6f-6db2-4cbf-ad64-d7a008a3c0ad": {"data": "Red", "locale": null, "channel": null, "attribute": "maincolor_color_e27aed6f-6db2-4cbf-ad64-d7a008a3c0ad"}}'),
	('color_bluestorm_c090c48d-4c14-43bd-afb2-b4f2a3abc984','bluestorm','color','{"image_color_491b2b80-474a-4254-a4ef-5f12ba30d6d": {"data": {"size": 5396, "filePath": "${bleuTempete}", "mimeType": "image/jpeg", "extension": "jpeg", "originalFilename": "bleuTempete.jpg"}, "locale": null, "channel": null, "attribute": "image_color_491b2b80-474a-4254-a4ef-5f12ba30d6d"},"label_color_491b2b80-474a-4254-a4ef-5f19ba10d8fc_en_US": {"data": "Blue storm", "locale": "en_US", "channel": null, "attribute":"label_color_491b2b80-474a-4254-a4ef-5f19ba10d8fc"}, "label_color_491b2b80-474a-4254-a4ef-5f19ba10d8fc_fr_FR": {"data": "Bleu tempête", "locale": "fr_FR", "channel": null, "attribute":"label_color_491b2b80-474a-4254-a4ef-5f19ba10d8fc"},"maincolor_color_e27aed6f-6db2-4cbf-ad64-d7a008a3c0ad": {"data": "Blue", "locale": null, "channel": null, "attribute": "maincolor_color_e27aed6f-6db2-4cbf-ad64-d7a008a3c0ad"}}'),
	('color_redchilli_8c28eaa5-673b-4f9c-b5d7-0f48f84e9322','redchilli','color','{"image_color_491b2b80-474a-4254-a4ef-5f12ba30d6d": {"data": {"size": 5396, "filePath": "${rougePiment}", "mimeType": "image/jpeg", "extension": "jpeg", "originalFilename": "rougePiment.jpg"}, "locale": null, "channel": null, "attribute": "image_color_491b2b80-474a-4254-a4ef-5f12ba30d6d"},"label_color_491b2b80-474a-4254-a4ef-5f19ba10d8fc_en_US": {"data": "Red chilli", "locale": "en_US", "channel": null, "attribute":"label_color_491b2b80-474a-4254-a4ef-5f19ba10d8fc"}, "label_color_491b2b80-474a-4254-a4ef-5f19ba10d8fc_fr_FR": {"data": "Rouge piment", "locale": "fr_FR", "channel": null, "attribute":"label_color_491b2b80-474a-4254-a4ef-5f19ba10d8fc"}}')
;
SET foreign_key_checks = 1;
SQL;
        $this->sqlConnection->executeUpdate($sql);
    }

    private function loadCountries(): void
    {
        $country = $this->uploadImage('country')->getKey();
        $sql = <<<SQL
SET foreign_key_checks = 0;
INSERT INTO `akeneo_reference_entity_reference_entity` (`identifier`, `labels`, `image`, `attribute_as_label`, `attribute_as_image`)
VALUES ('country','{\"en_US\": \"Country\"}', '${country}', 'label_country_491b2b80-474a-4254-a4ef-5f19ba12d8fc', 'image_country_491b2b80-474a-4254-a4ef-5f12ba30d6d');
INSERT INTO `akeneo_reference_entity_attribute` (
  `identifier`,
  `code`,
  `reference_entity_identifier`,
  `labels`,
  `attribute_type`,
  `attribute_order`,
  `is_required`,
  `value_per_channel`,
  `value_per_locale`,
  `additional_properties`
  )
VALUES
  ('label_country_491b2b80-474a-4254-a4ef-5f19ba12d8fc','label','country','{\"en_US\": \"Label\"}','text',1,0,0,1,'{\"max_length\": null, \"is_textarea\": false, \"validation_rule\": \"none\", \"regular_expression\": null, \"is_rich_text_editor\": false}'),
  ('image_country_491b2b80-474a-4254-a4ef-5f12ba30d6d','image','country','{\"en_US\": \"Image\"}','image',2,0,0,0,'{\"max_file_size\": \"10\", \"allowed_extensions\": [\"jpeg\", \"jpg\", \"png\"]}'),
  ('description_country_6219bc8d-4217-4590-9e2a-24e994e7c9d9','description','country','{\"en_US\": \"Description\"}','text',3,0,0,1,'{\"max_length\": null, \"is_textarea\": false, \"validation_rule\": \"none\", \"regular_expression\": null, \"is_rich_text_editor\": false}'),
  ('zone_country_628ef2db-65df-4267-a970-b8f64c881273','zone','country','{\"en_US\": \"Zone\"}','text',4,0,0,0,'{\"max_length\": null, \"is_textarea\": false, \"validation_rule\": \"none\", \"regular_expression\": null, \"is_rich_text_editor\": false}')
;
INSERT INTO `akeneo_reference_entity_record` (`identifier`, `code`, `reference_entity_identifier`, `value_collection`)
VALUES
  ('country_italia_08e78e66-4764-4def-8832-8cbc3102f200','italia','country','{"label_country_491b2b80-474a-4254-a4ef-5f19ba12d8f_en_US": {"data": "Italia", "locale": "en_US", "channel": null, "attribute":"label_country_491b2b80-474a-4254-a4ef-5f19ba12d8f"}}'),
  ('country_france_bac746d0-edfe-4ae1-82e0-16ce58a8dd90','france','country','{"label_country_491b2b80-474a-4254-a4ef-5f19ba12d8f_en_US": {"data": "France", "locale": "en_US", "channel": null, "attribute":"label_country_491b2b80-474a-4254-a4ef-5f19ba12d8f"}}')
;
SET foreign_key_checks = 1;
SQL;
        $this->sqlConnection->executeUpdate($sql);
    }

    private function loadMaterials(): void
    {
        $material = $this->uploadImage('material')->getKey();
        $sql = <<<SQL
SET foreign_key_checks = 0;
INSERT INTO `akeneo_reference_entity_reference_entity` (`identifier`, `labels`, `image`, `attribute_as_label`, `attribute_as_image`)
VALUES
('material','{\"en_US\": \"Material\"}', '${material}', 'label_material_491b2b80-474a-4254-a4ef-5f19ba12d8fc', 'image_material_491b2b80-474a-4254-a4ef-5f12ba30d6d');
INSERT INTO `akeneo_reference_entity_attribute` (
  `identifier`,
  `code`,
  `reference_entity_identifier`,
  `labels`,
  `attribute_type`,
  `attribute_order`,
  `is_required`,
  `value_per_channel`,
  `value_per_locale`,
  `additional_properties`
  )
VALUES
  ('label_material_491b2b80-474a-4254-a4ef-5f19ba12d8fc','label','material','{\"en_US\": \"Label\"}','text',1,0,0,1,'{\"max_length\": null, \"is_textarea\": false, \"validation_rule\": \"none\", \"regular_expression\": null, \"is_rich_text_editor\": false}'),
  ('image_material_491b2b80-474a-4254-a4ef-5f12ba30d6d','image','material','{\"en_US\": \"Image\"}','image',2,0,0,0,'{\"max_file_size\": \"10\", \"allowed_extensions\": [\"jpeg\", \"jpg\", \"png\"]}'),
  ('description_material_a36bfe43-7d80-4305-8a05-681f3d6d5ee6','description','material','{\"en_US\": \"Description\"}','text',3,0,0,1,'{\"max_length\": null, \"is_textarea\": true, \"validation_rule\": \"none\", \"regular_expression\": null, \"is_rich_text_editor\": true}')
  ;
SET foreign_key_checks = 1;
SQL;
        $this->sqlConnection->executeUpdate($sql);
    }

    private function uploadImage($code): FileInfoInterface
    {
        $path = sprintf('/../../Resources/fixtures/files/%s.jpg', $code);
        $rawFile = new \SplFileInfo(__DIR__ . $path);

        return $this->storer->store($rawFile, self::CATALOG_STORAGE_ALIAS);
    }

    private function indexRecords(): void
    {
        $this->commandLauncher->executeForeground(
            sprintf('%s %s', IndexRecordsCommand::INDEX_RECORDS_COMMAND_NAME, '--all')
        );
        $this->recordClient->refreshIndex();
    }
}
