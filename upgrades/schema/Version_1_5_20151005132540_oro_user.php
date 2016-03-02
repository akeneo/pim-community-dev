<?php

namespace Pim\Upgrade\schema;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Class Version_1_5_20151005132540_oro_user
 * This migration adds a column ui_locale_id for the oro_user table. Because the column will not be null,
 * the default value is generated in this order:
 * - if PIM config has a full locale (fr_FR, en_US), set it,
 * - if PIM config has locale (fr, en), we check if a full locale is available (fr_FR, en_US), and set it,
 * - if PIM config has locale (fr, en) but no available full locale, set first available locale,
 * - in other cases, set en_US.
 *
 * @author    Pierre Allard <pierre.allard@kosmopolead.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Version_1_5_20151005132540_oro_user extends AbstractMigration
{
    const CONFIG_VALUE_TABLE = 'oro_config_value';

    const CATALOG_LOCALE_TABLE = 'pim_catalog_locale';

    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $default_locale = $this->getDefaultCatalogLocaleId();

        $this->addSql('ALTER TABLE oro_user ADD ui_locale_id INT NOT NULL DEFAULT ' . $default_locale);
        $this->addSql('ALTER TABLE oro_user ADD CONSTRAINT FK_F82840BCF925933B FOREIGN KEY (ui_locale_id) REFERENCES pim_catalog_locale (id)');
        $this->addSql('CREATE INDEX IDX_F82840BCF925933B ON oro_user (ui_locale_id)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $this->throwIrreversibleMigrationException();
    }

    protected function getCatalogLocaleId($code, $like = false)
    {
        $query = "SELECT id FROM " . self::CATALOG_LOCALE_TABLE . " WHERE ";
        $query .= ($like ? "code LIKE '" . $code . "%'" : "code='" . $code . "' LIMIT 1");
        $stmt = $this->connection->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch();

        if (null !== $result) {
            return $result['id'];
        }

        return null;
    }

    protected function getDefaultFullLocale($code)
    {
        return ('en' == $code) ? 'en_US' : $code . '_' . strtoupper($code);
    }

    protected function getDefaultCatalogLocaleId()
    {
        $stmt = $this->connection->prepare("SELECT value FROM " . self::CONFIG_VALUE_TABLE . " WHERE name='language' AND section='oro_locale' LIMIT 1");
        $stmt->execute();
        $result = $stmt->fetch();
        if (null !== $result['value']) {
            $code = $result['value'];
            if (strpos($code, '_') !== false) {
                $result = $this->getCatalogLocaleId($code);
                if (null !== $result) {
                    return $result;
                }
            }

            $fullLocale = $this->getDefaultFullLocale($code);
            $result = $this->getCatalogLocaleId($fullLocale);
            if (null !== $result) {
                return $result;
            }

            $result = $this->getCatalogLocaleId($code, true);
            if (null !== $result) {
                return $result;
            }
        }

        return $this->getCatalogLocaleId('en_US');
    }
}
