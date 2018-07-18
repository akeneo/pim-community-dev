<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2017 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\EnrichedEntity\Infrastructure\Symfony\EventListener;

use Doctrine\DBAL\Connection;
use Pim\Bundle\InstallerBundle\Event\InstallerEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

/**
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class Installer implements EventSubscriberInterface
{
    /** @var Filesystem */
    private $filesystem;

    /** @var string */
    private $projectDir;

    /** @var Connection */
    private $dbal;

    /**
     * @param Filesystem $filesystem
     * @param string     $projectDir
     * @param Connection $dbal
     */
    public function __construct(Filesystem $filesystem, string $projectDir, Connection $dbal)
    {
        $this->filesystem = $filesystem;
        $this->projectDir = $projectDir;
        $this->dbal = $dbal;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [
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
DROP TABLE IF EXISTS `akeneo_enriched_entity_enriched_entity`;
CREATE TABLE `akeneo_enriched_entity_enriched_entity` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `identifier` VARCHAR(255) NOT NULL,
    `labels` JSON NOT NULL,
    PRIMARY KEY (`id`),
    UNIQUE `enriched_entity_identifier_index` (`identifier`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `akeneo_enriched_entity_record`;
CREATE TABLE `akeneo_enriched_entity_record` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `identifier` VARCHAR(255) NOT NULL,
    `enriched_entity_identifier` VARCHAR(255) NOT NULL,
    `labels` JSON NOT NULL,
    `data` JSON NOT NULL,
    PRIMARY KEY (`id`),
    UNIQUE `record_identifier_index` (`identifier`),
    CONSTRAINT enriched_entity_identifier_foreign_key FOREIGN KEY (`enriched_entity_identifier`) REFERENCES `akeneo_enriched_entity_enriched_entity` (identifier)
      ON DELETE CASCADE
      ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
SQL;

        $this->dbal->exec($sql);
    }

    public function loadFixtures(): void
    {
        $sql = <<<SQL
REPLACE INTO `akeneo_enriched_entity_enriched_entity` (`identifier`, `labels`)
VALUES
  ('designer', '{"en_US": "Designer", "fr_FR": "Concepteur"}'),
  ('brand', '{"fr_FR": "Marque"}');

REPLACE INTO `akeneo_enriched_entity_record` (`identifier`, `enriched_entity_identifier`, `labels`, `data`)
VALUES
  ('starck',   'designer', '{"en_US": "Philippe Starck"}', '{"description": "Famous for the design of the Freebox"}'),
  ('dyson',    'designer', '{"en_US": "James Dyson"}', '{"description": "James Dyson, creator of dyson"}'),
  ('newson',   'designer', '{"en_US": "Marc Newson"}', '{"description": "Born in australia"}'),
  ('vignelli', 'designer', '{"en_US": "Massimo Vignelli"}', '{"description": "Famous display designer"}'),
  ('arad',     'designer', '{"en_US": "Ron Arad"}', '{"description": "A designer close to the architectural world"}'),
  ('cogip',    'brand',    '{"fr_FR": "Cogip"}','{"country": "France"}'),
  ('sbep',     'brand',    '{"fr_FR": "La Société Belgo-Egyptienne d\'Élevage de Poulet"}','{"country": "egypt"}'),
  ('scep',     'brand',    '{"fr_FR": "Société Cairote d\'Élevage de Poulets"}','{"country": "egypt"}');
SQL;

        $this->dbal->exec($sql);
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
}
