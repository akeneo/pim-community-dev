<?php
declare(strict_types=1);

namespace Pim\Upgrade\schema;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class Version_4_0_202103181422_update_pim_catalog_completeness_id extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("ALTER TABLE pim_catalog_completeness MODIFY id bigint AUTO_INCREMENT");
    }

    public function down(Schema $schema)
    {
        $this->throwIrreversibleMigrationException();
    }
}
