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
        $this->loadRecords();
        $this->loadAttributes();
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
  ('baseline_brand_ed11f718-0e93-4d75-8965-21a5b4566b46','baseline','brand','{\"en_US\": \"Baseline\"}','text',8,0,0,1,'{\"max_length\": null, \"is_textarea\": false, \"validation_rule\": \"none\", \"regular_expression\": null, \"is_rich_text_editor\": false}'),
  ('birthdate_designer_87939c45-1d85-4134-9579-d594fff65030','birthdate','designer','{\"en_US\": \"Birthdate\"}','text',0,0,0,0,'{\"max_length\": null, \"is_textarea\": false, \"validation_rule\": \"none\", \"regular_expression\": null, \"is_rich_text_editor\": false}'),
  ('city_brand_d75161e7-765c-4523-9b87-0bd73ccf068f','city','brand','{\"en_US\": \"City\"}','text',1,0,0,0,'{\"max_length\": null, \"is_textarea\": false, \"validation_rule\": \"none\", \"regular_expression\": null, \"is_rich_text_editor\": false}'),
  ('country_city_29aea250-bc94-49b2-8259-bbc116410eb2','country','city','{\"en_US\": \"Country\"}','text',5,0,0,0,'{\"max_length\": null, \"is_textarea\": false, \"validation_rule\": \"none\", \"regular_expression\": null, \"is_rich_text_editor\": false}'),
  ('coverphoto_designer_e68f7b52-dfbc-4c5b-a316-73c83fdd841a','coverphoto','designer','{\"en_US\": \"Cover photo\"}','image',5,0,0,0,'{\"max_file_size\": \"10\", \"allowed_extensions\": [\"jpeg\", \"jpg\", \"png\"]}'),
  ('description_brand_081b677a-d662-4a3b-a843-aeeda4218154','description','brand','{\"en_US\": \"Description\"}','text',5,0,1,1,'{\"max_length\": null, \"is_textarea\": true, \"validation_rule\": \"none\", \"regular_expression\": null, \"is_rich_text_editor\": false}'),
  ('description_city_491b2b80-474a-4254-a4ef-5f12ba30d6fc','description','city','{\"en_US\": \"Description\"}','text',1,0,0,1,'{\"max_length\": null, \"is_textarea\": true, \"validation_rule\": \"none\", \"regular_expression\": null, \"is_rich_text_editor\": true}'),
  ('description_color_55f3847b-d5d7-4f33-bf14-a219ac6f29e4','description','color','{\"en_US\": \"description\"}','text',0,0,0,0,'{\"max_length\": null, \"is_textarea\": false, \"validation_rule\": \"none\", \"regular_expression\": null, \"is_rich_text_editor\": false}'),
  ('description_country_6219bc8d-4217-4590-9e2a-24e994e7c9d9','description','country','{\"en_US\": \"Description\"}','text',0,0,0,1,'{\"max_length\": null, \"is_textarea\": false, \"validation_rule\": \"none\", \"regular_expression\": null, \"is_rich_text_editor\": false}'),
  ('description_designer_1949c44b-f04c-46c1-9010-21a6d076f35b','description','designer','{\"en_US\": \"Description\"}','text',6,0,1,0,'{\"max_length\": null, \"is_textarea\": false, \"validation_rule\": \"none\", \"regular_expression\": null, \"is_rich_text_editor\": false}'),
  ('description_material_a36bfe43-7d80-4305-8a05-681f3d6d5ee6','description','material','{\"en_US\": \"Description\"}','text',0,0,0,1,'{\"max_length\": null, \"is_textarea\": true, \"validation_rule\": \"none\", \"regular_expression\": null, \"is_rich_text_editor\": true}'),
  ('founded_brand_fff5387e-64ce-4228-b68e-af8704867761','founded','brand','{\"en_US\": \"Founded\"}','text',3,0,0,0,'{\"max_length\": null, \"is_textarea\": false, \"validation_rule\": \"none\", \"regular_expression\": null, \"is_rich_text_editor\": false}'),
  ('founder_brand_eb505477-3139-4c6d-9014-ac8091c2c2de','founder','brand','{\"en_US\": \"Founder\"}','text',2,0,0,0,'{\"max_length\": null, \"is_textarea\": false, \"validation_rule\": \"none\", \"regular_expression\": null, \"is_rich_text_editor\": false}'),
  ('industry_brand_99e4cd39-3e56-4e2e-9f51-86ea1f3a66c0','industry','brand','{\"en_US\": \"Industry\"}','text',4,0,0,0,'{\"max_length\": null, \"is_textarea\": false, \"validation_rule\": \"none\", \"regular_expression\": null, \"is_rich_text_editor\": false}'),
  ('nationality_brand_f60def21-f44f-449d-9fae-7a4f76cded60','nationality','brand','{\"en_US\": \"Nationality\"}','text',0,0,0,0,'{\"max_length\": null, \"is_textarea\": false, \"validation_rule\": \"none\", \"regular_expression\": null, \"is_rich_text_editor\": false}'),
  ('nationality_designer_df90ead4-8aea-42a0-a517-5554e12631bb','nationality','designer','{\"en_US\": \"Nationality\"}','text',1,0,0,1,'{\"max_length\": null, \"is_textarea\": false, \"validation_rule\": \"none\", \"regular_expression\": null, \"is_rich_text_editor\": false}'),
  ('presentationvideo_brand_c61e8ef7-6606-40d7-810c-b2b94e92e68f','presentationvideo','brand','{\"en_US\": \"Presentation video\"}','text',6,0,0,1,'{\"max_length\": null, \"is_textarea\": false, \"validation_rule\": \"url\", \"regular_expression\": null, \"is_rich_text_editor\": false}'),
  ('region_city_d703b6ad-8a63-40a6-8771-f9b8c9567168','region','city','{\"en_US\": \"Region\"}','text',3,0,0,0,'{\"max_length\": null, \"is_textarea\": false, \"validation_rule\": \"none\", \"regular_expression\": null, \"is_rich_text_editor\": false}'),
  ('resume_designer_bad11eab-769b-49a1-a3e9-5a6366cc65dc','resume','designer','{\"en_US\": \"Résumé\"}','text',4,0,1,1,'{\"max_length\": null, \"is_textarea\": false, \"validation_rule\": \"none\", \"regular_expression\": null, \"is_rich_text_editor\": false}'),
  ('timezone_city_9944f4d6-06c0-484a-8a86-6fe32eefce7d','timezone','city','{\"en_US\": \"Timezone\"}','text',2,0,0,0,'{\"max_length\": null, \"is_textarea\": false, \"validation_rule\": \"none\", \"regular_expression\": null, \"is_rich_text_editor\": false}'),
  ('weather_city_65ccca01-fcb9-4a5c-ab63-a8a04ef6e2ed','weather','city','{\"en_US\": \"Weather\"}','text',4,0,0,0,'{\"max_length\": null, \"is_textarea\": false, \"validation_rule\": \"none\", \"regular_expression\": null, \"is_rich_text_editor\": false}'),
  ('website_brand_4272f9ec-e9ff-4734-878b-7f24fed422e2','website','brand','{\"en_US\": \"Website\"}','text',7,0,0,1,'{\"max_length\": null, \"is_textarea\": false, \"validation_rule\": \"url\", \"regular_expression\": null, \"is_rich_text_editor\": false}'),
  ('website_designer_5d008f13-a115-4147-8b7f-122a4f1d52d4','website','designer','{\"en_US\": \"Website\"}','text',2,0,0,0,'{\"max_length\": null, \"is_textarea\": false, \"validation_rule\": \"url\", \"regular_expression\": null, \"is_rich_text_editor\": false}'),
  ('wikipediapage_designer_8b2aa74c-cc2e-486f-a514-496800214bde','wikipediapage','designer','{\"en_US\": \"Wikipedia page\"}','text',3,0,0,1,'{\"max_length\": 5000, \"is_textarea\": true, \"validation_rule\": \"none\", \"regular_expression\": null, \"is_rich_text_editor\": true}'),
  ('zone_country_628ef2db-65df-4267-a970-b8f64c881273','zone','country','{\"en_US\": \"Zone\"}','text',1,0,0,0,'{\"max_length\": null, \"is_textarea\": false, \"validation_rule\": \"none\", \"regular_expression\": null, \"is_rich_text_editor\": false}');
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
  ('brand_cogip_6','cogip','brand','{"fr_FR": "Cogip"}',NULL,'{}'),
  ('brand_sbep_7','sbep','brand','{"fr_FR": "La Société Belgo-Egyptienne d\'Élevage de Poulet"}',NULL,'{}'),
  ('brand_scep_8','scep','brand','{"fr_FR": "Société Cairote d\'Élevage de Poulets"}',NULL,'{}'),
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
  ('city_new_york_b5150405-4cd6-4743-905f-641d4191d16d','new_york','city','{"en_US": "New-York"}','3/1/7/b/317bbac1566d57f4ef2e903cb755757100fe47f7_newyork.jpg','{}')
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
        $arguments = ['command' => 'akeneo:reference-entity:index-records'];

        $input = new ArrayInput($arguments);
        if (0 !== $command->run($input, new NullOutput())) {
            throw new \RuntimeException('Something went wrong while indexing');
        }
    }
}
