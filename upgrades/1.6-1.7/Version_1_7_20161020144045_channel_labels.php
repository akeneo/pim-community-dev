<?php

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Create table "pim_catalog_channel_translation"
 * Remove "label" in "pim_catalog_channel" table
 */
class Version_1_7_20161020144045_channel_labels extends AbstractMigration implements ContainerAwareInterface
{
    const CHANNEL_TABLE = 'pim_catalog_channel';
    const LOCALE_TABLE = 'pim_catalog_locale';

    /** @var ContainerInterface */
    protected $container;

    /**
     * {@inheritdoc}
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $query = sprintf('SELECT id, label FROM %s', self::CHANNEL_TABLE);
        $stmt = $this->connection->prepare($query);
        $stmt->execute();
        $channels = $stmt->fetchAll();

        $this->addSql(<<<SQL
            CREATE TABLE IF NOT EXISTS `pim_catalog_channel_translation` (
              `id` INT AUTO_INCREMENT NOT NULL,
              `foreign_key` int(11) DEFAULT NULL,
              `label` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
              `locale` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
              PRIMARY KEY(id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

            ALTER TABLE `pim_catalog_channel_translation`
            ADD UNIQUE KEY `locale_foreign_key_idx` (`locale`,`foreign_key`), ADD KEY `IDX_8A91679D7E366551` (`foreign_key`);

            ALTER TABLE `pim_catalog_channel_translation`
            ADD CONSTRAINT `FK_8A91679D7E366551` FOREIGN KEY (`foreign_key`) REFERENCES `pim_catalog_channel` (`id`) ON DELETE CASCADE;
SQL
        );

        $query = sprintf('SELECT id, code FROM %s WHERE is_activated = 1', self::LOCALE_TABLE);
        $stmt = $this->connection->prepare($query);
        $stmt->execute();
        $locales = $stmt->fetchAll();

        foreach ($channels as $channel) {
            foreach ($locales as $locale) {
                $sql = 'INSERT INTO `pim_catalog_channel_translation` (`foreign_key`, `label`, `locale`) VALUES ';
                $sql.= sprintf('(%s, "%s", "%s")', $channel['id'], $channel['label'], $locale['code']);

                $this->addSql($sql);
            }
        }

        $this->addSql(sprintf('ALTER TABLE %s DROP label', self::CHANNEL_TABLE));
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $this->throwIrreversibleMigrationException();
    }
}
