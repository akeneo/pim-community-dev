<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2017 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\EnrichedEntity\Infrastructure\Symfony\Command;

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
 * This commands reset the database fixtures for the enriched entity.
 * It also is an event listener used during the PIM isntallation.
 *
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class EnrichedEntityResetFixturesCommand extends ContainerAwareCommand implements EventSubscriberInterface
{
    private const RESET_FIXTURES_COMMAND_NAME = 'akeneo:enriched-entity:reset-fixtures';
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
            ->setDescription('Resets the fixtures of the enriched entity bounded context.')
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
        $targetDir = $this->projectDir.'/web/bundles/akeneoenrichedentity';
        if ($event->getArgument('symlink')) {
            $this->relativeSymlinkWithFallback($originDir, $targetDir);
        } else {
            $this->hardCopy($originDir, $targetDir);
        }
    }

    public function createSchema(): void
    {
        $sql = <<<SQL
DROP TABLE IF EXISTS `akeneo_enriched_entity_attribute`;
DROP TABLE IF EXISTS `akeneo_enriched_entity_record`;
DROP TABLE IF EXISTS `akeneo_enriched_entity_enriched_entity`;

CREATE TABLE `akeneo_enriched_entity_enriched_entity` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `identifier` VARCHAR(255) NOT NULL,
    `labels` JSON NOT NULL,
    `image` VARCHAR(255) NULL,
    PRIMARY KEY (`id`),
    UNIQUE `akeneoenriched_entity_enriched_entity_identifier_index` (`identifier`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `akeneo_enriched_entity_record` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `identifier` VARCHAR(255) NOT NULL,
    `code` VARCHAR(255) NOT NULL,
    `enriched_entity_identifier` VARCHAR(255) NOT NULL,
    `labels` JSON NOT NULL,
    `image` VARCHAR(255) NULL,
    `value_collection` JSON NOT NULL,
    PRIMARY KEY (`id`),
    UNIQUE `akeneoenriched_entity_record_identifier_index` (`identifier`, `enriched_entity_identifier`),
    CONSTRAINT akeneoenriched_entity_enriched_entity_identifier_foreign_key FOREIGN KEY (`enriched_entity_identifier`) REFERENCES `akeneo_enriched_entity_enriched_entity` (identifier)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `akeneo_enriched_entity_attribute` (
    `identifier` VARCHAR(255) NOT NULL,
    `code` VARCHAR(255) NOT NULL,
    `enriched_entity_identifier` VARCHAR(255) NOT NULL,
    `labels` JSON NOT NULL,
    `attribute_type` VARCHAR(255) NOT NULL,
    `attribute_order` INT NOT NULL,
    `is_required` BOOLEAN NOT NULL,
    `value_per_channel` BOOLEAN NOT NULL,
    `value_per_locale` BOOLEAN NOT NULL,
    `additional_properties` JSON NOT NULL,
    PRIMARY KEY (`identifier`),
    UNIQUE `attribute_identifier_index` (`code`, `enriched_entity_identifier`),
    UNIQUE `attribute_enriched_entity_order_index` (`enriched_entity_identifier`, `attribute_order`),
    CONSTRAINT attribute_enriched_entity_identifier_foreign_key FOREIGN KEY (`enriched_entity_identifier`) REFERENCES `akeneo_enriched_entity_enriched_entity` (identifier)
      ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL;

        $this->dbal->exec($sql);
    }

    public function loadFixtures(): void
    {
        $this->loadEnrichedEntities();
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

    private function uploadEnrichedEntityImage($code): FileInfoInterface
    {
        $path = sprintf('/../Resources/fixtures/files/%s.jpg', $code);
        $rawFile = new \SplFileInfo(__DIR__.$path);

        return $this->storer->store($rawFile, self::CATALOG_STORAGE_ALIAS);
    }

    private function loadEnrichedEntities(): void
    {
        $designer = $this->uploadEnrichedEntityImage('designer');
        $brand = $this->uploadEnrichedEntityImage('brand');

        $sql = <<<SQL
INSERT INTO `akeneo_enriched_entity_enriched_entity` (`identifier`, `labels`, `image`)
VALUES
  ('designer', '{"en_US": "Designer", "fr_FR": "Concepteur"}', :designer),
  ('brand', '{"fr_FR": "Marque"}', :brand);
SQL;
        $affectedRows = $this->dbal->executeUpdate($sql, [
            'designer' => $designer->getKey(),
            'brand' => $brand->getKey()
        ]);
        if (0 === $affectedRows) {
            throw new \LogicException('An issue occured while installing the enriched entities.');
        }
    }

    private function loadRecords(): void
    {
        $starck_image = $this->uploadEnrichedEntityImage('philippe_starck');
        $arad_image = $this->uploadEnrichedEntityImage('ron_arad');

        $sql = <<<SQL
INSERT INTO `akeneo_enriched_entity_record` (`identifier`, `code`, `enriched_entity_identifier`, `labels`, `value_collection`, image)
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
INSERT INTO `akeneo_enriched_entity_attribute` (
  `identifier`,
  `code`,
  `enriched_entity_identifier`,
  `labels`,
  `attribute_type`,
  `attribute_order`,
  `is_required`,
  `value_per_channel`,
  `value_per_locale`,
  `additional_properties`
  )
VALUES
  ('name_designer_16f624b3-0855-4e12-80b6-da077252a194',      'name',        'designer', '{"en_US": "Name", "fr_FR": "Nom"}', 'text',  1, false, false, true, '{"max_length": null, "is_textarea": false, "validation_rule": null, "regular_expression": null, "is_rich_text_editor": false}'),
  ('portrait_designer_1781b92b-6785-4bdf-9837-9f0db68902d4',  'portrait',    'designer', '{"en_US": "Portrait"}',             'image', 2, false, false, false, '{"max_file_size": "30.01", "allowed_extensions": ["png", "jpg"]}'),
  ('name_brand_90440ddf-109d-4114-8668-e6a1da98dc38',         'name',        'brand',    '{"en_US": "Name", "fr_FR": "Nom"}', 'text',  1, false, true, false, '{"max_length": null, "is_textarea": false, "validation_rule": null, "regular_expression": null, "is_rich_text_editor": false}'),
  ('description_brand_befbca68-b613-4839-a1aa-5f74f98c438a',  'description', 'brand',    '{"en_US": "Description"}',          'text',  2, false, true, true, '{"max_length": 255, "is_textarea": true, "validation_rule": null, "regular_expression": null, "is_rich_text_editor": false}'),
  ('image_designer_d00e1ee1-6c3d-4280-ae45-b124994491f2',     'image',       'designer', '{"en_US": "Image"}',                'image', 3, false, false, true,  '{"max_file_size": "30.01", "allowed_extensions": ["png", "jpg"]}')
SQL;

        $affectedRows = $this->dbal->exec($sql);
        if (0 === $affectedRows) {
            throw new \LogicException('An issue occured while installing the enriched entities.');
        }
    }
}
