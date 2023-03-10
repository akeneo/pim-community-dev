<?php

declare(strict_types=1);

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * @copyright 2023 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class Version_8_0_20230308145000_set_empty_categories_labels_to_null extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Set empty labels to null on pim_catalog_category_translation';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(
            <<<SQL
            UPDATE pim_catalog_category_translation
            SET label=NULL
            WHERE label = '';
            SQL,
        );
    }

    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException();
    }
}
