<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2017 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\ReferenceEntity\Infrastructure\Symfony\Command;

use Akeneo\Platform\Bundle\InstallerBundle\Event\InstallerEvents;
use Akeneo\Tool\Component\FileStorage\File\FileStorerInterface;
use Akeneo\Tool\Component\FileStorage\Model\FileInfoInterface;
use Doctrine\DBAL\Connection;
use Ramsey\Uuid\Uuid;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

/**
 * This commands reset the database fixtures for the reference entity.
 * It also is an event listener used during the PIM isntallation.
 *
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ReferenceEntityResetFixturesCommand extends ContainerAwareCommand implements EventSubscriberInterface
{
    private const RESET_FIXTURES_COMMAND_NAME = 'akeneo:reference-entity:reset-fixtures';
    private const CATALOG_STORAGE_ALIAS = 'catalogStorage';
    private const NUMBER_OF_FAKE_RECORD_TO_CREATE = 10000;

    /** @var Filesystem */
    private $filesystem;

    /** @var string */
    private $projectDir;

    /** @var Connection */
    private $dbal;

    /** @var FileStorerInterface */
    private $storer;

    public function __construct(Filesystem $filesystem, string $projectDir, Connection $dbal, FileStorerInterface $storer)
    {
        parent::__construct(self::RESET_FIXTURES_COMMAND_NAME);

        $this->filesystem = $filesystem;
        $this->projectDir = $projectDir;
        $this->dbal = $dbal;
        $this->storer = $storer;

        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName(self::RESET_FIXTURES_COMMAND_NAME)
            ->setDescription('Resets the fixtures of the reference entity bounded context.')
            ->setHidden(true);
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->createSchema();
        $this->loadFixtures();
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [
            InstallerEvents::POST_SYMFONY_ASSETS_DUMP => ['installAssets'],
            InstallerEvents::POST_ASSETS_DUMP => ['installAssets'],
            InstallerEvents::POST_DB_CREATE => ['createSchema'],
            InstallerEvents::POST_LOAD_FIXTURES => ['loadFixtures'],
        ];
    }

    public function installAssets(GenericEvent $event): void
    {
        $originDir = __DIR__.'/../../../../front';
        $targetDir = $this->projectDir.'/web/bundles/akeneoreferenceentity';
        if ($event->getArgument('symlink')) {
            $this->relativeSymlinkWithFallback($originDir, $targetDir);
        } else {
            $this->hardCopy($originDir, $targetDir);
        }
    }

    public function createSchema(): void
    {
        $sql = <<<SQL
DROP TABLE IF EXISTS `akeneo_reference_entity_attribute`;
DROP TABLE IF EXISTS `akeneo_reference_entity_record`;
DROP TABLE IF EXISTS `akeneo_reference_entity_reference_entity`;

CREATE TABLE `akeneo_reference_entity_reference_entity` (
    `identifier` VARCHAR(255) NOT NULL,
    `labels` JSON NOT NULL,
    `image` VARCHAR(255) NULL,
    PRIMARY KEY (`identifier`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE akeneo_reference_entity_record (
    identifier VARCHAR(255) NOT NULL,
    code VARCHAR(255) NOT NULL,
    reference_entity_identifier VARCHAR(255) NOT NULL,
    labels JSON NOT NULL,
    image VARCHAR(255) NULL,
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
    UNIQUE `attribute_reference_entity_order_index` (`reference_entity_identifier`, `attribute_order`),
    CONSTRAINT attribute_reference_entity_identifier_foreign_key FOREIGN KEY (`reference_entity_identifier`) REFERENCES `akeneo_reference_entity_reference_entity` (identifier)
      ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL;

        $this->dbal->exec($sql);
    }

    public function loadFixtures(): void
    {
        $this->loadReferenceEntities();
        $this->loadAttributes();
        $this->loadRecords();
    }

    /**
     * Try to create relative symlink.
     *
     * Falling back to absolute symlink and finally hard copy.
     *
     * @param string $originDir
     * @param string $targetDir
     */
    private function relativeSymlinkWithFallback(string $originDir, string $targetDir): void
    {
        try {
            $this->symlink($originDir, $targetDir, true);
        } catch (IOException $e) {
            $this->absoluteSymlinkWithFallback($originDir, $targetDir);
        }
    }

    /**
     * Try to create absolute symlink.
     *
     * Falling back to hard copy.
     *
     * @param string $originDir
     * @param string $targetDir
     */
    private function absoluteSymlinkWithFallback(string $originDir, string $targetDir): void
    {
        try {
            $this->symlink($originDir, $targetDir);
        } catch (IOException $e) {
            // fall back to copy
            $this->hardCopy($originDir, $targetDir);
        }
    }

    /**
     * Creates symbolic link.
     *
     * @param string $originDir
     * @param string $targetDir
     * @param bool   $relative
     *
     * @throws IOException if link can not be created
     */
    private function symlink(string $originDir, string $targetDir, bool $relative = false): void
    {
        if ($relative) {
            $this->filesystem->mkdir(dirname($targetDir));
            $originDir = $this->filesystem->makePathRelative($originDir, realpath(dirname($targetDir)));
        }
        $this->filesystem->symlink($originDir, $targetDir);
        if (!file_exists($targetDir)) {
            throw new IOException(sprintf('Symbolic link "%s" was created but appears to be broken.', $targetDir), 0, null, $targetDir);
        }
    }

    /**
     * Copies origin to target.
     *
     * @param string $originDir
     * @param string $targetDir
     */
    private function hardCopy(string $originDir, string $targetDir): void
    {
        $this->filesystem->mkdir($targetDir, 0777);
        // We use a custom iterator to ignore VCS files
        $this->filesystem->mirror($originDir, $targetDir, Finder::create()->ignoreDotFiles(false)->in($originDir));
    }

    private function uploadImage($code): FileInfoInterface
    {
        $path = sprintf('/../Resources/fixtures/files/%s.jpg', $code);
        $rawFile = new \SplFileInfo(__DIR__.$path);

        return $this->storer->store($rawFile, self::CATALOG_STORAGE_ALIAS);
    }

    private function loadReferenceEntities(): void
    {
        $designer = $this->uploadImage('designer');
        $brand = $this->uploadImage('brand');
        $color = $this->uploadImage('color');
        $material = $this->uploadImage('material');
        $city = $this->uploadImage('city');
        $country = $this->uploadImage('country');

        $sql = <<<SQL
INSERT INTO `akeneo_reference_entity_reference_entity` (`identifier`, `labels`, `image`)
VALUES
('designer','{\"en_US\": \"Designer\", \"fr_FR\": \"Concepteur\"}', :designer),
('brand','{\"en_US\": \"Brand\", \"fr_FR\": \"Marque\"}', :brand),
('color','{\"en_US\": \"Color\"}', :color),
('material','{\"en_US\": \"Material\"}', :material),
('city','{\"en_US\": \"City\"}', :city),
('country','{\"en_US\": \"Country\"}', :country);
SQL;
        $affectedRows = $this->dbal->executeUpdate($sql, [
            'designer' => $designer->getKey(),
            'brand' => $brand->getKey(),
            'color' => $color->getKey(),
            'material' => $material->getKey(),
            'city' => $city->getKey(),
            'country' => $country->getKey(),
        ]);
        if (0 === $affectedRows) {
            throw new \LogicException('An issue occured while installing the reference entities.');
        }
    }

    private function loadRecords(): void
    {
        $this->insertRecords();
        $this->indexRecords();
    }

    private function generateFakeCity(): string
    {
        $fakeCity = <<<SQL
('city_%s_%s','%s%s','city','{"en_US": "%s"}', NULL,'{"region_city_d703b6ad-8a63-40a6-8771-f9b8c9567168": {"data": "%s", "locale": null, "channel": null, "attribute": "region_city_d703b6ad-8a63-40a6-8771-f9b8c9567168"}, "country_city_29aea250-bc94-49b2-8259-bbc116410eb2": {"data": "France", "locale": null, "channel": null, "attribute": "country_city_29aea250-bc94-49b2-8259-bbc116410eb2"}, "weather_city_65ccca01-fcb9-4a5c-ab63-a8a04ef6e2ed": {"data": "%s°C, Wind E at %s km/h, 19 Humidity", "locale": null, "channel": null, "attribute": "weather_city_65ccca01-fcb9-4a5c-ab63-a8a04ef6e2ed"}, "timezone_city_9944f4d6-06c0-484a-8a86-6fe32eefce7d": {"data": "Western European Summer Time", "locale": null, "channel": null, "attribute": "timezone_city_9944f4d6-06c0-484a-8a86-6fe32eefce7d"}, "description_city_491b2b80-474a-4254-a4ef-5f12ba30d6fc_en_US": {"data": "<p><strong>Want to live a charming getaway?</strong></p><p>Forget Venice and discover %s! You will fall for THE trendy and romantic city trip ... %s will charm you with its history, architecture, culture and people. Be enchanted by the soul of %s, strolling through the steep streets with colorful facades of Alfama or Bairro Alto. Admire the sublime views of the Tagus and the Atlantic Ocean from the viewpoints accessible by yellow tram ... There is so much to discover in %s and so little to do to escape the time of a weekend full of poetry . Do not wait any longer !</p>", "locale": "en_US", "channel": null, "attribute": "description_city_491b2b80-474a-4254-a4ef-5f12ba30d6fc"}}')
SQL;
        $cityCodes = [
            'z_nantes',
            'z_vertou',
            'z_niort',
            'z_limoge',
            'z_bordeaux',
            'z_toulouse',
            'z_dijon',
            'z_digouin',
            'z_puy_en_velay',
            'z_marseille',
            'z_olonne_sur_mer',
            'z_montaigu',
            'z_lanion',
            'z_rennes',
        ];
        $code = $cityCodes[array_rand($cityCodes)];
        $label = str_replace('_', ' ', ucfirst($code));
        $temp = rand(5, 30);
        $speed = rand(10, 50);
        $uuid = Uuid::uuid4()->toString();

        return sprintf($fakeCity, $code, $uuid, $code, str_replace('-', '_', $uuid), $label, $label, $temp, $speed, $label, $label, $label, $label);
    }

    private function loadAttributes(): void
    {
        $sql = <<<SQL
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
  ('birthdate_designer_87939c45-1d85-4134-9579-d594fff65030','birthdate','designer','{\"en_US\": \"Birthdate\"}','text',0,0,0,0,'{\"max_length\": null, \"is_textarea\": false, \"validation_rule\": \"none\", \"regular_expression\": null, \"is_rich_text_editor\": false}'),
  ('country_city_29aea250-bc94-49b2-8259-bbc116410eb2','country','city','{\"en_US\": \"Country\"}','text',5,0,0,0,'{\"max_length\": null, \"is_textarea\": false, \"validation_rule\": \"none\", \"regular_expression\": null, \"is_rich_text_editor\": false}'),
  ('coverphoto_designer_e68f7b52-dfbc-4c5b-a316-73c83fdd841a','coverphoto','designer','{\"en_US\": \"Cover photo\"}','image',5,0,0,0,'{\"max_file_size\": \"10\", \"allowed_extensions\": [\"jpeg\", \"jpg\", \"png\"]}'),
  ('description_city_491b2b80-474a-4254-a4ef-5f12ba30d6fc','description','city','{\"en_US\": \"Description\"}','text',1,0,0,1,'{\"max_length\": null, \"is_textarea\": true, \"validation_rule\": \"none\", \"regular_expression\": null, \"is_rich_text_editor\": true}'),
  ('description_color_55f3847b-d5d7-4f33-bf14-a219ac6f29e4','description','color','{\"en_US\": \"description\"}','text',0,0,0,0,'{\"max_length\": null, \"is_textarea\": false, \"validation_rule\": \"none\", \"regular_expression\": null, \"is_rich_text_editor\": false}'),
  ('description_country_6219bc8d-4217-4590-9e2a-24e994e7c9d9','description','country','{\"en_US\": \"Description\"}','text',0,0,0,1,'{\"max_length\": null, \"is_textarea\": false, \"validation_rule\": \"none\", \"regular_expression\": null, \"is_rich_text_editor\": false}'),
  ('description_designer_1949c44b-f04c-46c1-9010-21a6d076f35b','description','designer','{\"en_US\": \"Description\"}','text',6,0,1,0,'{\"max_length\": null, \"is_textarea\": false, \"validation_rule\": \"none\", \"regular_expression\": null, \"is_rich_text_editor\": false}'),
  ('description_material_a36bfe43-7d80-4305-8a05-681f3d6d5ee6','description','material','{\"en_US\": \"Description\"}','text',0,0,0,1,'{\"max_length\": null, \"is_textarea\": true, \"validation_rule\": \"none\", \"regular_expression\": null, \"is_rich_text_editor\": true}'),
  ('nationality_designer_df90ead4-8aea-42a0-a517-5554e12631bb','nationality','designer','{\"en_US\": \"Nationality\"}','text',1,0,0,1,'{\"max_length\": null, \"is_textarea\": false, \"validation_rule\": \"none\", \"regular_expression\": null, \"is_rich_text_editor\": false}'),
  ('region_city_d703b6ad-8a63-40a6-8771-f9b8c9567168','region','city','{\"en_US\": \"Region\"}','text',3,0,0,0,'{\"max_length\": null, \"is_textarea\": false, \"validation_rule\": \"none\", \"regular_expression\": null, \"is_rich_text_editor\": false}'),
  ('resume_designer_bad11eab-769b-49a1-a3e9-5a6366cc65dc','resume','designer','{\"en_US\": \"Résumé\"}','text',4,0,1,1,'{\"max_length\": null, \"is_textarea\": false, \"validation_rule\": \"none\", \"regular_expression\": null, \"is_rich_text_editor\": false}'),
  ('timezone_city_9944f4d6-06c0-484a-8a86-6fe32eefce7d','timezone','city','{\"en_US\": \"Timezone\"}','text',2,0,0,0,'{\"max_length\": null, \"is_textarea\": false, \"validation_rule\": \"none\", \"regular_expression\": null, \"is_rich_text_editor\": false}'),
  ('weather_city_65ccca01-fcb9-4a5c-ab63-a8a04ef6e2ed','weather','city','{\"en_US\": \"Weather\"}','text',4,0,0,0,'{\"max_length\": null, \"is_textarea\": false, \"validation_rule\": \"none\", \"regular_expression\": null, \"is_rich_text_editor\": false}'),
  ('website_designer_5d008f13-a115-4147-8b7f-122a4f1d52d4','website','designer','{\"en_US\": \"Website\"}','text',2,0,0,0,'{\"max_length\": null, \"is_textarea\": false, \"validation_rule\": \"url\", \"regular_expression\": null, \"is_rich_text_editor\": false}'),
  ('wikipediapage_designer_8b2aa74c-cc2e-486f-a514-496800214bde','wikipediapage','designer','{\"en_US\": \"Wikipedia page\"}','text',3,0,0,1,'{\"max_length\": 5000, \"is_textarea\": true, \"validation_rule\": \"none\", \"regular_expression\": null, \"is_rich_text_editor\": true}'),
  ('zone_country_628ef2db-65df-4267-a970-b8f64c881273','zone','country','{\"en_US\": \"Zone\"}','text',1,0,0,0,'{\"max_length\": null, \"is_textarea\": false, \"validation_rule\": \"none\", \"regular_expression\": null, \"is_rich_text_editor\": false}'),
  ('description_brand_d794e371-5d9b-4c4b-918b-9714a9b65a71','description','brand','{\"en_US\": \"Description\", \"fr_FR\": \"Description\"}','text',5,0,1,1,'{\"max_length\": null, \"is_textarea\": true, \"validation_rule\": \"none\", \"regular_expression\": null, \"is_rich_text_editor\": true}'),
  ('founded_brand_fff5387e-64ce-4228-b68e-af8704867761','founded','brand','{\"en_US\": \"Founded\", \"fr_FR\": \"Fondé\"}','text',3,0,0,0,'{\"max_length\": null, \"is_textarea\": false, \"validation_rule\": \"none\", \"regular_expression\": null, \"is_rich_text_editor\": false}'),
  ('founder_brand_eb505477-3139-4c6d-9014-ac8091c2c2de','founder','brand','{\"en_US\": \"Designer\", \"fr_FR\": \"Designer\"}','text',2,0,0,0,'{\"max_length\": null, \"is_textarea\": false, \"validation_rule\": \"none\", \"regular_expression\": null, \"is_rich_text_editor\": false}'),
  ('nationality_brand_f60def21-f44f-449d-9fae-7a4f76cded60','nationality','brand','{\"en_US\": \"Country\", \"fr_FR\": \"Pays\"}','text',0,0,0,0,'{\"max_length\": null, \"is_textarea\": false, \"validation_rule\": \"none\", \"regular_expression\": null, \"is_rich_text_editor\": false}'),
  ('photo_brand_8587cda6-58c8-47fa-9278-033e1d8c735c','photo','brand','{\"en_US\": \"Photo\", \"fr_FR\": \"Photo\"}','image',4,0,0,0,'{\"max_file_size\": null, \"allowed_extensions\": []}')
  ;
SQL;

        $affectedRows = $this->dbal->exec($sql);
        if (0 === $affectedRows) {
            throw new \LogicException('An issue occured while installing the reference entities.');
        }
    }

    private function insertRecords(): void
    {
        $philippeStarck = $this->uploadImage('philippeStarck');
        $ronArad = $this->uploadImage('ronArad');
        $this->insertBrands();

        $baseSql = <<<SQL
INSERT INTO `akeneo_reference_entity_record` (`identifier`, `code`, `reference_entity_identifier`, `labels`, `image`, `value_collection`)
VALUES
%s;
SQL;

        $baseRecords = <<<SQL
  ('designer_starck_1','starck','designer','{"en_US": "Philippe Starck"}', :philippeStarck,'{"birthdate_designer_87939c45-1d85-4134-9579-d594fff65030": {"data": "12", "locale": null, "channel": null, "attribute": "birthdate_designer_87939c45-1d85-4134-9579-d594fff65030"}, "nationality_designer_df90ead4-8aea-42a0-a517-5554e12631bb_en_US": {"data": "French", "locale": "en_US", "channel": null, "attribute": "nationality_designer_df90ead4-8aea-42a0-a517-5554e12631bb"}, "wikipediapage_designer_8b2aa74c-cc2e-486f-a514-496800214bde_en_US": {"data": "nice", "locale": "en_US", "channel": null, "attribute": "wikipediapage_designer_8b2aa74c-cc2e-486f-a514-496800214bde"}}'),
  ('designer_dyson_2','dyson','designer','{"en_US": "James Dyson"}','7/3/a/b/73ab8e03ed7ae3a30da0e7fa2c1fc14285aa07bb_jd_cutout.png','{}'),
  ('designer_newson_3','newson','designer','{"en_US": "Marc Newson"}','9/b/5/4/9b54c493ff515b74cd61eb21db0e2dcf0adf483c_Dezeen_Marc_Newson_1.jpg','{}'),
  ('designer_vignelli_4','vignelli','designer','{"en_US": "Massimo Vignelli"}',NULL,'{}'),
  ('designer_arad_5','arad','designer','{"en_US": "Ron Arad"}', :ronArad,'{}'),
  ('color_blue_9023e45d-c063-453a-85b4-e2693d3796ec','blue','color','{"en_US": "Blue"}',NULL,'{"description_color_55f3847b-d5d7-4f33-bf14-a219ac6f29e4": {"data": "lorem", "locale": null, "channel": null, "attribute": "description_color_55f3847b-d5d7-4f33-bf14-a219ac6f29e4"}}'),
  ('color_green_5d962dbf-227a-48cc-8002-5170bb82f290','green','color','{"en_US": "Green"}',NULL,'{}'),
  ('color_grey_cc1b5457-9517-4c41-acf6-aaa4609afbef','grey','color','{"en_US": "Grey"}',NULL,'{}'),
  ('color_red_103c25dc-ca7e-4168-83d2-4e30e49be84d','red','color','{"en_US": "Red"}',NULL,'{}'),
  ('city_roma_ee07911a-cd91-426c-89f0-5525c26f7467','roma','city','{"en_US": "Roma"}','2/9/c/0/29c0e0e09f222d94efd8063f7c775abb918540e9_roma.jpg','{"region_city_d703b6ad-8a63-40a6-8771-f9b8c9567168": {"data": "Lazio", "locale": null, "channel": null, "attribute": "region_city_d703b6ad-8a63-40a6-8771-f9b8c9567168"}, "country_city_29aea250-bc94-49b2-8259-bbc116410eb2": {"data": "Italia", "locale": null, "channel": null, "attribute": "country_city_29aea250-bc94-49b2-8259-bbc116410eb2"}, "weather_city_65ccca01-fcb9-4a5c-ab63-a8a04ef6e2ed": {"data": "26°C, Wind NW at 8 km/h, 34% Humidity", "locale": null, "channel": null, "attribute": "weather_city_65ccca01-fcb9-4a5c-ab63-a8a04ef6e2ed"}, "timezone_city_9944f4d6-06c0-484a-8a86-6fe32eefce7d": {"data": "Central European Summer Time", "locale": null, "channel": null, "attribute": "timezone_city_9944f4d6-06c0-484a-8a86-6fe32eefce7d"}, "description_city_491b2b80-474a-4254-a4ef-5f12ba30d6fc_en_US": {"data": "Roma description", "locale": "en_US", "channel": null, "attribute": "description_city_491b2b80-474a-4254-a4ef-5f12ba30d6fc"}}'),
  ('city_lisbon_6198d4cd-342f-4d75-9261-f862a9a23b18','lisbon','city','{"en_US": "Lisbon"}','f/b/9/8/fb989fb0c40b76f96815b207fb2b423f6ea1bf80_lisbon.jpg','{"region_city_d703b6ad-8a63-40a6-8771-f9b8c9567168": {"data": "Lisbon", "locale": null, "channel": null, "attribute": "region_city_d703b6ad-8a63-40a6-8771-f9b8c9567168"}, "country_city_29aea250-bc94-49b2-8259-bbc116410eb2": {"data": "Portugal", "locale": null, "channel": null, "attribute": "country_city_29aea250-bc94-49b2-8259-bbc116410eb2"}, "weather_city_65ccca01-fcb9-4a5c-ab63-a8a04ef6e2ed": {"data": "28°C, Wind E at 6 km/h, 19% Humidity", "locale": null, "channel": null, "attribute": "weather_city_65ccca01-fcb9-4a5c-ab63-a8a04ef6e2ed"}, "timezone_city_9944f4d6-06c0-484a-8a86-6fe32eefce7d": {"data": "Western European Summer Time", "locale": null, "channel": null, "attribute": "timezone_city_9944f4d6-06c0-484a-8a86-6fe32eefce7d"}, "description_city_491b2b80-474a-4254-a4ef-5f12ba30d6fc_en_US": {"data": "<p><strong>Want to live a charming getaway?</strong></p><p>Forget Venice and discover Lisbon! You will fall for THE trendy and romantic city trip ... Lisbon will charm you with its history, architecture, culture and people. Be enchanted by the soul of Lisbon, strolling through the steep streets with colorful facades of Alfama or Bairro Alto. Admire the sublime views of the Tagus and the Atlantic Ocean from the viewpoints accessible by yellow tram ... There is so much to discover in Lisbon and so little to do to escape the time of a weekend full of poetry . Do not wait any longer !</p>", "locale": "en_US", "channel": null, "attribute": "description_city_491b2b80-474a-4254-a4ef-5f12ba30d6fc"}}'),
  ('city_cannes_01afdc3e-3ecf-4a86-85ef-e81b2d6e95fd','cannes','city','{"en_US": "Cannes"}','d/f/4/b/df4bc64ad17e891a07fc38565e1fb7a5f7e73e36_Cannes.jpg','{"country_city_29aea250-bc94-49b2-8259-bbc116410eb2": {"data": "France", "locale": null, "channel": null, "attribute": "country_city_29aea250-bc94-49b2-8259-bbc116410eb2"}}'),
  ('city_paris_42b05a3c-e811-47ce-88fe-21aa03479c2f','paris','city','{"en_US": "Paris"}','a/f/c/f/afcf5634bbdd5ec34567add3cbbe93ae89906e07_Paris.jpg','{"country_city_29aea250-bc94-49b2-8259-bbc116410eb2": {"data": "France", "locale": null, "channel": null, "attribute": "country_city_29aea250-bc94-49b2-8259-bbc116410eb2"}}'),
  ('country_italia_08e78e66-4764-4def-8832-8cbc3102f200','italia','country','{"en_US": "Italia"}',NULL,'{}'),
  ('country_france_bac746d0-edfe-4ae1-82e0-16ce58a8dd90','france','country','{"en_US": "France"}',NULL,'{}'),
  ('city_new_york_b5150405-4cd6-4743-905f-641d4191d16d','new_york','city','{"en_US": "New-York"}','3/1/7/b/317bbac1566d57f4ef2e903cb755757100fe47f7_newyork.jpg','{}');
SQL;
        $affectedRows = $this->dbal->executeUpdate(sprintf($baseSql, $baseRecords), [
            'philippeStarck' => $philippeStarck->getKey(),
            'ronArad'        => $ronArad->getKey(),
        ]);
        if (0 === $affectedRows) {
            throw new \LogicException('An issue occured while installing the records.');
        }

        $fakeCities = [];
        for ($i = 1; $i < self::NUMBER_OF_FAKE_RECORD_TO_CREATE; $i++) {
            if (0 === ($i % 2000)) {
                $this->dbal->executeUpdate(sprintf($baseSql, implode($fakeCities, ',')));
                $fakeCities = [];
            }
            $fakeCities[] = $this->generateFakeCity();
        }
        if (!empty($fakeCities)) {
            $this->dbal->executeUpdate(sprintf($baseSql, implode($fakeCities, ',')));
        }
    }

    private function indexRecords(): void
    {
        $command = $this->getApplication()->find('akeneo:reference-entity:index-records');
        $arguments = ['command' => 'akeneo:reference-entity:index-records', '--all' => true, '--env' => $this->getContainer()->getParameter('kernel.environment')];

        $input = new ArrayInput($arguments);
        if (0 !== $command->run($input, new NullOutput())) {
            throw new \RuntimeException('Something went wrong while indexing');
        }
    }

    private function insertBrands(): void
    {
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
INSERT INTO `akeneo_reference_entity_record` (`identifier`, `code`, `reference_entity_identifier`, `labels`, `image`, `value_collection`)
VALUES
  ('brand_alessi_dc1c552a-108c-4e1d-9d72-7f17368bdb5a','alessi','brand','{"en_US": "Alessi", "fr_FR": "Alessi"}', '${alessi}','{"photo_brand_8587cda6-58c8-47fa-9278-033e1d8c735c": {"data": {"size": 168107, "filePath": "${alessiProducts}", "mimeType": "image/jpeg", "extension": "jpg", "originalFilename": "alessi_products.jpg"}, "locale": null, "channel": null, "attribute": "photo_brand_8587cda6-58c8-47fa-9278-033e1d8c735c"}, "founded_brand_fff5387e-64ce-4228-b68e-af8704867761": {"data": "1921", "locale": null, "channel": null, "attribute": "founded_brand_fff5387e-64ce-4228-b68e-af8704867761"}, "founder_brand_eb505477-3139-4c6d-9014-ac8091c2c2de": {"data": "Marcel Wanders", "locale": null, "channel": null, "attribute": "founder_brand_eb505477-3139-4c6d-9014-ac8091c2c2de"}, "nationality_brand_f60def21-f44f-449d-9fae-7a4f76cded60": {"data": "Italy", "locale": null, "channel": null, "attribute": "nationality_brand_f60def21-f44f-449d-9fae-7a4f76cded60"}, "description_brand_d794e371-5d9b-4c4b-918b-9714a9b65a71_ecommerce_en_US": {"data": "<p><span style=\\\"color: rgb(102,102,102);background-color: rgb(255,255,255);font-size: 11px;font-family: Arial, Helvetica, sans-serif;\\\">Alessi is truly a \\\"dream factory\\\"! This famous Italian brand has been enhancing our daily lives for more than 80 years thanks to its beautiful and functional items which are designed by leading architects and designers. At Alessi, design has been a family affair since 1921. Initially focusing on coffee services and trays, Alessi acquired international popularity during the 1950s through working with renowned architects and designers such as Ettore Sottsass.</span>&nbsp;</p>\\\\n", "locale": "en_US", "channel": "ecommerce", "attribute": "description_brand_d794e371-5d9b-4c4b-918b-9714a9b65a71"}, "description_brand_d794e371-5d9b-4c4b-918b-9714a9b65a71_ecommerce_fr_FR": {"data": "<p><strong>A</strong><span style=\\\"color: rgb(102,102,102);background-color: rgb(255,255,255);font-size: 11px;font-family: Arial, Helvetica, sans-serif;\\\">lessi est une véritable \\\"Usine à rêves\\\" ! Cette célèbre marque italienne sublime notre quotidien depuis plus de 80 ans avec des objets beaux et fonctionnels, dessinés par les plus grands architectes et designers. Chez Alessi, le design, c\\'est une histoire de famille depuis 1921. Se concentrant au départ sur les services à café et les plateaux, Alessi acquiert dès les années 1950 une popularité internationale en collaborant  avec des architectes et designers de renom tel que</span><a href=\\\"https://www.madeindesign.com/d-ettore-sottsass.html\\\" target=\\\"_self\\\"> <span style=\\\"color: rgb(102,102,102);background-color: rgb(255,255,255);font-size: 11px;font-family: Arial, Helvetica, sans-serif;\\\">Ettore Sottsass</span></a></p>\\\\n", "locale": "fr_FR", "channel": "ecommerce", "attribute": "description_brand_d794e371-5d9b-4c4b-918b-9714a9b65a71"}}'),
  ('brand_bangolufsen_bea84235-fd91-423b-8058-ff9e2dd1490a','bangolufsen','brand','{"en_US": "Bang & Olufsen", "fr_FR": "Bang & Olufsen"}', '${bangolufsen}','{"photo_brand_8587cda6-58c8-47fa-9278-033e1d8c735c": {"data": {"size": 32580, "filePath": "${bangolufsenProducts}", "mimeType": "image/jpeg", "extension": "jpg", "originalFilename": "b&o_products.jpg"}, "locale": null, "channel": null, "attribute": "photo_brand_8587cda6-58c8-47fa-9278-033e1d8c735c"}, "founded_brand_fff5387e-64ce-4228-b68e-af8704867761": {"data": "1925", "locale": null, "channel": null, "attribute": "founded_brand_fff5387e-64ce-4228-b68e-af8704867761"}, "founder_brand_eb505477-3139-4c6d-9014-ac8091c2c2de": {"data": "Cecilie Manz", "locale": null, "channel": null, "attribute": "founder_brand_eb505477-3139-4c6d-9014-ac8091c2c2de"}, "nationality_brand_f60def21-f44f-449d-9fae-7a4f76cded60": {"data": "Denmark", "locale": null, "channel": null, "attribute": "nationality_brand_f60def21-f44f-449d-9fae-7a4f76cded60"}, "description_brand_d794e371-5d9b-4c4b-918b-9714a9b65a71_ecommerce_en_US": {"data": "<p style=\\\"text-align:justify;\\\"><span style=\\\"color: rgb(9,30,66);background-color: rgb(255,255,255);font-size: 14px;font-family: -apple-system, system-ui, \\\"Segoe UI\\\", Roboto, \\\"Noto Sans\\\", Ubuntu, \\\"Droid Sans\\\", \\\"Helvetica Neue\\\", sans-serif;\\\">B&amp;O PLAY delivers stand-alone products with clear and simple operations - portable products that are intuitive to use, easy to integrate into your daily life, and deliver excellent high-quality experiences.</span></p>\\\\n<p style=\\\"text-align:justify;\\\"><span style=\\\"color: rgb(9,30,66);background-color: rgb(255,255,255);font-size: 14px;font-family: -apple-system, system-ui, \\\"Segoe UI\\\", Roboto, \\\"Noto Sans\\\", Ubuntu, \\\"Droid Sans\\\", \\\"Helvetica Neue\\\", sans-serif;\\\"> ‘’We want to evoke senses, to elevate the experience of listening and watching. We have spoken to musicians and studio recorders who all love the fact that more people listen to music in more places, but hate the fact that the quality of the listening experience has been eroded. We want to provide the opportunity to experience media in a convenient and easy way but still in outstanding high quality.  Firmly grounded in our 88-year history in Bang &amp; Olufsen, we interpret the same core values for a new type of contemporary products.\\\"</span></p>\\\\n", "locale": "en_US", "channel": "ecommerce", "attribute": "description_brand_d794e371-5d9b-4c4b-918b-9714a9b65a71"}}'),
  ('brand_fatboy_a00d1caa-7d86-412d-9263-2b3410f31029','fatboy','brand','{"en_US": "Fatboy", "fr_FR": "Fatboy"}', '${fatboy}','{"photo_brand_8587cda6-58c8-47fa-9278-033e1d8c735c": {"data": {"size": 176910, "filePath": "${fatboyProducts}", "mimeType": "image/jpeg", "extension": "jpg", "originalFilename": "fatboy_products.jpg"}, "locale": null, "channel": null, "attribute": "photo_brand_8587cda6-58c8-47fa-9278-033e1d8c735c"}, "founded_brand_fff5387e-64ce-4228-b68e-af8704867761": {"data": "1998", "locale": null, "channel": null, "attribute": "founded_brand_fff5387e-64ce-4228-b68e-af8704867761"}, "founder_brand_eb505477-3139-4c6d-9014-ac8091c2c2de": {"data": "Alex Bergman", "locale": null, "channel": null, "attribute": "founder_brand_eb505477-3139-4c6d-9014-ac8091c2c2de"}, "nationality_brand_f60def21-f44f-449d-9fae-7a4f76cded60": {"data": "Netherlands", "locale": null, "channel": null, "attribute": "nationality_brand_f60def21-f44f-449d-9fae-7a4f76cded60"}, "description_brand_d794e371-5d9b-4c4b-918b-9714a9b65a71_ecommerce_en_US": {"data": "<p><span style=\\\"color: rgb(102,102,102);background-color: rgb(255,255,255);font-size: 11px;font-family: Arial, Helvetica, sans-serif;\\\">These 21st Century beanbags combine high quality with exclusive cheekiness. Inspired by the music of Fatboy Slim, the Finnish designer, Jukka Setalla appeared to be on the cutting edge of design. But it was Dutchman, Alex Bergman who revolutionized the product in 2002 by creating a brand around it, proving that the Fatboy could indeed sell itself. A strategy of the over-sized life, the Fatboy family tree includes gigantic lamps, magical hammocks, and umbrellas, bringing about the ‘wonder-fuller life’.</span></p>\\\\n", "locale": "en_US", "channel": "ecommerce", "attribute": "description_brand_d794e371-5d9b-4c4b-918b-9714a9b65a71"}}'),
  ('brand_fermob_57362b29-d213-4656-aec2-875a182e3561','fermob','brand','{"en_US": "Fermob", "fr_FR": "Fermob"}', '${fermob}','{"photo_brand_8587cda6-58c8-47fa-9278-033e1d8c735c": {"data": {"size": 162758, "filePath": "${fermobProducts}", "mimeType": "image/jpeg", "extension": "jpg", "originalFilename": "fermob_products.jpg"}, "locale": null, "channel": null, "attribute": "photo_brand_8587cda6-58c8-47fa-9278-033e1d8c735c"}, "founded_brand_fff5387e-64ce-4228-b68e-af8704867761": {"data": "1989", "locale": null, "channel": null, "attribute": "founded_brand_fff5387e-64ce-4228-b68e-af8704867761"}, "founder_brand_eb505477-3139-4c6d-9014-ac8091c2c2de": {"data": "Pascal Mourgue", "locale": null, "channel": null, "attribute": "founder_brand_eb505477-3139-4c6d-9014-ac8091c2c2de"}, "nationality_brand_f60def21-f44f-449d-9fae-7a4f76cded60": {"data": "France", "locale": null, "channel": null, "attribute": "nationality_brand_f60def21-f44f-449d-9fae-7a4f76cded60"}, "description_brand_d794e371-5d9b-4c4b-918b-9714a9b65a71_ecommerce_de_DE": {"data": "", "locale": "de_DE", "channel": "ecommerce", "attribute": "description_brand_d794e371-5d9b-4c4b-918b-9714a9b65a71"}, "description_brand_d794e371-5d9b-4c4b-918b-9714a9b65a71_ecommerce_en_US": {"data": "<p><span style=\\\"color: rgb(102,102,102);background-color: rgb(255,255,255);font-size: 11px;font-family: Arial, Helvetica, sans-serif;\\\">A French outdoor furniture brand that breathes creative spirit into the design process, Fermob has been working with established and up &amp; coming designers from all walks of life for over 20 years. From Fermob’s factory in Thoissey, discover how technology and process are combined to produce high-quality furniture that is definitively made in France. Fermob’s commitment to the environment, know-how &amp; production is revealed in furniture inspired by places and events, like the Luxembourg or Biarritz series as well as the 60s or 1900 chair, which highlight both the elegance and refinement of a philosophy just waiting to be discovered.</span></p>\\\\n", "locale": "en_US", "channel": "ecommerce", "attribute": "description_brand_d794e371-5d9b-4c4b-918b-9714a9b65a71"}}'),
  ('brand_kartell_79505e53-9694-47e1-aa5d-d8812c5ed699','kartell','brand','{"en_US": "Kartell", "fr_FR": "Kartell"}', '${kartell}','{"photo_brand_8587cda6-58c8-47fa-9278-033e1d8c735c": {"data": {"size": 88980, "filePath": "${kartellProducts}", "mimeType": "image/jpeg", "extension": "jpg", "originalFilename": "kartell_products.jpg"}, "locale": null, "channel": null, "attribute": "photo_brand_8587cda6-58c8-47fa-9278-033e1d8c735c"}, "founded_brand_fff5387e-64ce-4228-b68e-af8704867761": {"data": "1949", "locale": null, "channel": null, "attribute": "founded_brand_fff5387e-64ce-4228-b68e-af8704867761"}, "founder_brand_eb505477-3139-4c6d-9014-ac8091c2c2de": {"data": "Philippe Starck", "locale": null, "channel": null, "attribute": "founder_brand_eb505477-3139-4c6d-9014-ac8091c2c2de"}, "nationality_brand_f60def21-f44f-449d-9fae-7a4f76cded60": {"data": "Italy", "locale": null, "channel": null, "attribute": "nationality_brand_f60def21-f44f-449d-9fae-7a4f76cded60"}, "description_brand_d794e371-5d9b-4c4b-918b-9714a9b65a71_ecommerce_en_US": {"data": "<p><span style=\\\"color: rgb(102,102,102);background-color: rgb(255,255,255);font-size: 11px;font-family: Arial, Helvetica, sans-serif;\\\">‘’Kartell - The Culture of Plastics’’… In just over 50 years, this famous Italian company has revolutionised plastic, elevating it and propelling it into the refined world of luxury. Today, Kartell has more than a hundred showrooms all over the world and a good number of its creations have become cult pieces on display in the most prestigious museums. The famous Kartell Louis Ghost armchair has the most sales for armchairs in the world, with 1.5 million sales! Challenging the material, constantly researching new tactile, visual and aesthetic effects - Kartell faces every challenge!</span>&nbsp;</p>\\\\n", "locale": "en_US", "channel": "ecommerce", "attribute": "description_brand_d794e371-5d9b-4c4b-918b-9714a9b65a71"}, "description_brand_d794e371-5d9b-4c4b-918b-9714a9b65a71_ecommerce_fr_FR": {"data": "<p style=\\\"text-align:justify;\\\"><span style=\\\"color: rgb(102,102,102);background-color: rgb(255,255,255);font-size: 11px;font-family: Arial, Helvetica, sans-serif;\\\">Entreprise leader du design, fondée à Milan en 1949 par Giulio Castelli, Kartell est depuis près de 70 ans une des entreprises symbole de la conception Made in Italy. Une histoire pleine de succès racontée à travers un incroyable éventail de produits - meubles, accessoires de décoration, éclairage - devenus partie intégrante du paysage domestique, et même de véritables icônes du design contemporain.</span></p>\\\\n<p style=\\\"text-align:justify;\\\"><span style=\\\"color: rgb(102,102,102);background-color: rgb(255,255,255);font-size: 11px;font-family: Arial, Helvetica, sans-serif;\\\">Depuis 1988, Claudio Luti, l’héritier de l’«esprit Kartell», insuffle une prodigieuse dynamique à la marque.</span>&nbsp;</p>\\\\n", "locale": "fr_FR", "channel": "ecommerce", "attribute": "description_brand_d794e371-5d9b-4c4b-918b-9714a9b65a71"}}'),
  ('brand_lexon_4c3e8f80-dda4-456e-862e-59dc6e2e02f6','lexon','brand','{"en_US": "Lexon", "fr_FR": "Lexon"}', '${lexon}','{"photo_brand_8587cda6-58c8-47fa-9278-033e1d8c735c": {"data": {"size": 34100, "filePath": "${lexonProducts}", "mimeType": "image/jpeg", "extension": "jpg", "originalFilename": "lexon_products.jpg"}, "locale": null, "channel": null, "attribute": "photo_brand_8587cda6-58c8-47fa-9278-033e1d8c735c"}, "founded_brand_fff5387e-64ce-4228-b68e-af8704867761": {"data": "1991", "locale": null, "channel": null, "attribute": "founded_brand_fff5387e-64ce-4228-b68e-af8704867761"}, "founder_brand_eb505477-3139-4c6d-9014-ac8091c2c2de": {"data": "Marc Berthier", "locale": null, "channel": null, "attribute": "founder_brand_eb505477-3139-4c6d-9014-ac8091c2c2de"}, "nationality_brand_f60def21-f44f-449d-9fae-7a4f76cded60": {"data": "France", "locale": null, "channel": null, "attribute": "nationality_brand_f60def21-f44f-449d-9fae-7a4f76cded60"}, "description_brand_d794e371-5d9b-4c4b-918b-9714a9b65a71_ecommerce_en_US": {"data": "<p><span style=\\\"color: rgb(102,102,102);background-color: rgb(255,255,255);font-size: 11px;font-family: Arial, Helvetica, sans-serif;\\\">For over 20 years, Lexon has been pioneering creative solutions in the world of objects and communication by balancing a unique artistic and industrial equation. Innovative, functional, well designed and manufactured, their products are both timeless and functional. Based on a simple philosophy: to delight and be useful. Lexon products start with imagination, are realised through effective design and find their way to enhance your desk and workspace.</span></p>\\\\n", "locale": "en_US", "channel": "ecommerce", "attribute": "description_brand_d794e371-5d9b-4c4b-918b-9714a9b65a71"}}'),
  ('brand_muuto_00fb9223-8636-4707-aa43-9058acfdfbe4','muuto','brand','{"en_US": "Muuto", "fr_FR": "Muuto"}', '${muuto}','{"photo_brand_8587cda6-58c8-47fa-9278-033e1d8c735c": {"data": {"size": 118854, "filePath": "${muutoProducts}", "mimeType": "image/jpeg", "extension": "jpg", "originalFilename": "muuto_products.jpg"}, "locale": null, "channel": null, "attribute": "photo_brand_8587cda6-58c8-47fa-9278-033e1d8c735c"}, "founded_brand_fff5387e-64ce-4228-b68e-af8704867761": {"data": "2008", "locale": null, "channel": null, "attribute": "founded_brand_fff5387e-64ce-4228-b68e-af8704867761"}, "founder_brand_eb505477-3139-4c6d-9014-ac8091c2c2de": {"data": "Anderssen & Voll", "locale": null, "channel": null, "attribute": "founder_brand_eb505477-3139-4c6d-9014-ac8091c2c2de"}, "nationality_brand_f60def21-f44f-449d-9fae-7a4f76cded60": {"data": "Denmark", "locale": null, "channel": null, "attribute": "nationality_brand_f60def21-f44f-449d-9fae-7a4f76cded60"}, "description_brand_d794e371-5d9b-4c4b-918b-9714a9b65a71_ecommerce_en_US": {"data": "<p><span style=\\\"color: rgb(102,102,102);background-color: rgb(255,255,255);font-size: 11px;font-family: Arial, Helvetica, sans-serif;\\\">Founded in 2008, this young Danish manufacturing enterprise represents the new wave in Scandinavian design. There are two young designers behind Muuto, who have set out to develop traditional Scandinavian design in a new and original context. To achieve this, they have called on the creativity of a new generation of Nordic designers such as Louise Campbell, From Us With Love, Norway Says and Ole Jensen. Muuto takes the very best of Scandinavian talent and gives them total freedom to express their personal vision for an object in daily life. The result: a simple salt shaker becomes a work of art, stacked cylinders form a bookcase and a vase takes the form of cups balanced on top of one...</span></p>\\\\n", "locale": "en_US", "channel": "ecommerce", "attribute": "description_brand_d794e371-5d9b-4c4b-918b-9714a9b65a71"}}'),
  ('brand_tomdixon_076948af-6b73-4844-80f3-1a033998874b','tomdixon','brand','{"en_US": "Tom Dixon", "fr_FR": "Tom Dixon"}', '${tomdixon}','{"photo_brand_8587cda6-58c8-47fa-9278-033e1d8c735c": {"data": {"size": 29189, "filePath": "${tomdixonProducts}", "mimeType": "image/jpeg", "extension": "jpg", "originalFilename": "tom-dixon_products.jpg"}, "locale": null, "channel": null, "attribute": "photo_brand_8587cda6-58c8-47fa-9278-033e1d8c735c"}, "founded_brand_fff5387e-64ce-4228-b68e-af8704867761": {"data": "2002", "locale": null, "channel": null, "attribute": "founded_brand_fff5387e-64ce-4228-b68e-af8704867761"}, "founder_brand_eb505477-3139-4c6d-9014-ac8091c2c2de": {"data": "Tom Dixon", "locale": null, "channel": null, "attribute": "founder_brand_eb505477-3139-4c6d-9014-ac8091c2c2de"}, "nationality_brand_f60def21-f44f-449d-9fae-7a4f76cded60": {"data": "United Kingdom", "locale": null, "channel": null, "attribute": "nationality_brand_f60def21-f44f-449d-9fae-7a4f76cded60"}, "description_brand_d794e371-5d9b-4c4b-918b-9714a9b65a71_ecommerce_en_US": {"data": "<p style=\\\"text-align:justify;\\\"></p>\\\\n<p><span style=\\\"color: rgb(102,102,102);background-color: rgb(255,255,255);font-size: 11px;font-family: Arial, Helvetica, sans-serif;\\\">Tom Dixon is one of the most original British designers of his generation; and a rebel of the Avant Garde of international design! Awarded the title \\\"Designer of the Year\\\" in 2006, with exhibits in museums throughout the whole world, this self-taught designer has had a truly unusual career. In 1980 Dixon was a rock guitarist, working as a DJ in a club at night, and by day learning how to solder with a friend who ran a garage. He started his career by making furniture from recycled metal. Twenty years later, this self-made man is famous throughout the whole world. After having been artistic director at Habitat, he set up his own firm to make his collections of lighting...</span><br></p>\\\\n", "locale": "en_US", "channel": "ecommerce", "attribute": "description_brand_d794e371-5d9b-4c4b-918b-9714a9b65a71"}}');
SQL;

        $affectedRows = $this->dbal->executeUpdate($sql);

        if (0 === $affectedRows) {
            throw new \LogicException('An issue occured while installing the records.');
        }
    }
}
