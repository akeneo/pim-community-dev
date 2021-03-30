<?php

declare(strict_types=1);

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class Version_6_0_20210330143635_sanitize_users_having_link_to_subcategory extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        $this->addSql(<<<SQL
            UPDATE oro_user ou,
                (
                    SELECT ou.id, pcc.root as defaultTree_id
                    FROM oro_user ou
                         INNER JOIN pim_catalog_category pcc on ou.defaultTree_id = pcc.id
                    WHERE pcc.parent_id IS NOT NULL
                ) new_user_tree
            SET ou.defaultTree_id = new_user_tree.defaultTree_id
            WHERE ou.id = new_user_tree.id;
        SQL);
    }

    public function down(Schema $schema) : void
    {
        $this->throwIrreversibleMigrationException();
    }
}
