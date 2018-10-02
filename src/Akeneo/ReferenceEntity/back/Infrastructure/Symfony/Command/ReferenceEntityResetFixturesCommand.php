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
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
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

CREATE TABLE `akeneo_reference_entity_record` (
    `identifier` VARCHAR(255) NOT NULL,
    `code` VARCHAR(255) NOT NULL,
    `reference_entity_identifier` VARCHAR(255) NOT NULL,
    `labels` JSON NOT NULL,
    `image` VARCHAR(255) NULL,
    `value_collection` JSON NOT NULL,
    PRIMARY KEY (`identifier`),
    UNIQUE `akeneoreference_entity_record_identifier_index` (`code`, `reference_entity_identifier`),
    CONSTRAINT akeneoreference_entity_reference_entity_identifier_foreign_key FOREIGN KEY (`reference_entity_identifier`) REFERENCES `akeneo_reference_entity_reference_entity` (identifier)
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

    private function uploadReferenceEntityImage($code): FileInfoInterface
    {
        $path = sprintf('/../Resources/fixtures/files/%s.jpg', $code);
        $rawFile = new \SplFileInfo(__DIR__.$path);

        return $this->storer->store($rawFile, self::CATALOG_STORAGE_ALIAS);
    }

    private function loadReferenceEntities(): void
    {
        $designer = $this->uploadReferenceEntityImage('designer');
        $brand = $this->uploadReferenceEntityImage('brand');

        $sql = <<<SQL
INSERT INTO `akeneo_reference_entity_reference_entity` (`identifier`, `labels`, `image`)
VALUES
  ('designer', '{"en_US": "Designer", "fr_FR": "Concepteur"}', :designer),
  ('brand', '{"fr_FR": "Marque"}', :brand);
SQL;
        $affectedRows = $this->dbal->executeUpdate($sql, [
            'designer' => $designer->getKey(),
            'brand' => $brand->getKey()
        ]);
        if (0 === $affectedRows) {
            throw new \LogicException('An issue occured while installing the reference entities.');
        }
    }

    private function loadRecords(): void
    {
        $starck_image = $this->uploadReferenceEntityImage('philippe_starck');
        $arad_image = $this->uploadReferenceEntityImage('ron_arad');

        $sql = <<<SQL
INSERT INTO `akeneo_reference_entity_record` (`identifier`, `code`, `reference_entity_identifier`, `labels`, `value_collection`, image)
VALUES
  ('designer_starck_1', 'starck', 'designer', '{"en_US": "Philippe Starck"}', '{"description": "Famous for the design of the Freebox"}', :starck_image),
  ('designer_dyson_2',  'dyson', 'designer', '{"en_US": "James Dyson"}', '{"description": "James Dyson, creator of dyson"}', NULL),
  ('designer_newson_3', 'newson', 'designer', '{"en_US": "Marc Newson"}', '{"description": "Born in australia"}', NULL),
  ('designer_vignelli_4', 'vignelli', 'designer', '{"en_US": "Massimo Vignelli"}', '{"description": "Famous display designer"}', NULL),
  ('designer_arad_5', 'arad', 'designer', '{"en_US": "Ron Arad"}', '{"description": "A designer close to the architectural world"}', :arad_image),
  ('brand_cogip_6', 'cogip', 'brand',    '{"fr_FR": "Cogip"}','{"country": "France"}', NULL),
  ('brand_sbep_7', 'sbep', 'brand', '{"fr_FR": "La Société Belgo-Egyptienne d\'Élevage de Poulet"}','{"country": "egypt"}', NULL),
  ('brand_scep_8', 'scep', 'brand', '{"fr_FR": "Société Cairote d\'Élevage de Poulets"}','{"country": "egypt"}', NULL);
SQL;
        $affectedRows = $this->dbal->executeUpdate($sql, [
            'starck_image' => $starck_image->getKey(),
            'arad_image' => $arad_image->getKey(),
        ]);
        if (0 === $affectedRows) {
            throw new \LogicException('An issue occured while installing the records.');
        }
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
  ('baseline_brand_ed11f718-0e93-4d75-8965-21a5b4566b46','baseline','brand','{"en_US": "Baseline"}','text',8,0,0,1,'{"max_length": null, "is_textarea": false, "validation_rule": "none", "regular_expression": null, "is_rich_text_editor": false}'),
  ('birthdate_designer_87939c45-1d85-4134-9579-d594fff65030','birthdate','designer','{"en_US": "Birthdate"}','text',0,0,0,0,'{"max_length": null, "is_textarea": false, "validation_rule": "none", "regular_expression": null, "is_rich_text_editor": false}'),
  ('city_brand_d75161e7-765c-4523-9b87-0bd73ccf068f','city','brand','{"en_US": "City"}','text',1,0,0,0,'{"max_length": null, "is_textarea": false, "validation_rule": "none", "regular_expression": null, "is_rich_text_editor": false}'),
  ('coverphoto_designer_e68f7b52-dfbc-4c5b-a316-73c83fdd841a','coverphoto','designer','{"en_US": "Cover photo"}','image',5,0,0,0,'{"max_file_size": "10", "allowed_extensions": ["jpeg", "jpg", "png"]}'),
  ('description_brand_081b677a-d662-4a3b-a843-aeeda4218154','description','brand','{"en_US": "Description"}','text',5,0,1,1,'{"max_length": null, "is_textarea": true, "validation_rule": "none", "regular_expression": null, "is_rich_text_editor": true}'),
  ('founded_brand_fff5387e-64ce-4228-b68e-af8704867761','founded','brand','{"en_US": "Founded"}','text',3,0,0,0,'{"max_length": null, "is_textarea": false, "validation_rule": "none", "regular_expression": null, "is_rich_text_editor": false}'),
  ('founder_brand_eb505477-3139-4c6d-9014-ac8091c2c2de','founder','brand','{"en_US": "Founder"}','text',2,0,0,0,'{"max_length": null, "is_textarea": false, "validation_rule": "none", "regular_expression": null, "is_rich_text_editor": false}'),
  ('industry_brand_99e4cd39-3e56-4e2e-9f51-86ea1f3a66c0','industry','brand','{"en_US": "Industry"}','text',4,0,0,0,'{"max_length": null, "is_textarea": false, "validation_rule": "none", "regular_expression": null, "is_rich_text_editor": false}'),
  ('nationality_brand_f60def21-f44f-449d-9fae-7a4f76cded60','nationality','brand','{"en_US": "Nationality"}','text',0,0,0,0,'{"max_length": null, "is_textarea": false, "validation_rule": "none", "regular_expression": null, "is_rich_text_editor": false}'),
  ('nationality_designer_df90ead4-8aea-42a0-a517-5554e12631bb','nationality','designer','{"en_US": "Nationality"}','text',1,0,0,1,'{"max_length": null, "is_textarea": false, "validation_rule": "none", "regular_expression": null, "is_rich_text_editor": false}'),
  ('presentationvideo_brand_c61e8ef7-6606-40d7-810c-b2b94e92e68f','presentationvideo','brand','{"en_US": "Presentation video"}','text',6,0,0,1,'{"max_length": null, "is_textarea": false, "validation_rule": "url", "regular_expression": null, "is_rich_text_editor": false}'),
  ('resume_designer_bad11eab-769b-49a1-a3e9-5a6366cc65dc','resume','designer','{"en_US": "Résumé"}','text',4,0,1,1,'{"max_length": null, "is_textarea": false, "validation_rule": "none", "regular_expression": null, "is_rich_text_editor": false}'),
  ('website_brand_4272f9ec-e9ff-4734-878b-7f24fed422e2','website','brand','{"en_US": "Website"}','text',7,0,0,1,'{"max_length": null, "is_textarea": false, "validation_rule": "url", "regular_expression": null, "is_rich_text_editor": false}'),
  ('website_designer_5d008f13-a115-4147-8b7f-122a4f1d52d4','website','designer','{"en_US": "Website"}','text',2,0,0,0,'{"max_length": null, "is_textarea": false, "validation_rule": "url", "regular_expression": null, "is_rich_text_editor": false}'),
  ('wikipediapage_designer_8b2aa74c-cc2e-486f-a514-496800214bde','wikipediapage','designer','{"en_US": "Wikipedia page"}','text',3,0,0,1,'{"max_length": 5000, "is_textarea": true, "validation_rule": "none", "regular_expression": null, "is_rich_text_editor": true}');
SQL;

        $affectedRows = $this->dbal->exec($sql);
        if (0 === $affectedRows) {
            throw new \LogicException('An issue occured while installing the reference entities.');
        }
    }
}
