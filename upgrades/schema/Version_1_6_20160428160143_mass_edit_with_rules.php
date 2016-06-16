<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2016 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Add the new mass edit jobs that apply rules in "akeneo_batch_job_instance"
 *
 * @author Adrien Pétremann <adrien.petremann@akeneo.com>
 */
class Version_1_6_20160428160143_mass_edit_with_rules extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql(<<<SQL
        INSERT INTO `akeneo_batch_job_instance`
            (`code`, `label`, `alias`, `status`, `connector`, `rawConfiguration`, `type`)
        VALUES
            ('update_product_value_with_permission_and_rules', 'Mass update products values with permission check & rules application', 'update_product_value_with_permission_and_rules', 0, 'Akeneo Mass Edit Connector', 'a:3:{s:7:\"filters\";a:0:{}s:18:\"realTimeVersioning\";b:1;s:7:\"actions\";a:0:{}}', 'mass_edit'),
            ('add_product_value_with_permission_and_rules', 'Mass add products values with permission check & rules application', 'add_product_value_with_permission_and_rules', 0, 'Akeneo Mass Edit Connector', 'a:3:{s:7:\"filters\";a:0:{}s:18:\"realTimeVersioning\";b:1;s:7:\"actions\";a:0:{}}', 'mass_edit'),
            ('edit_common_attributes_with_permission_and_rules', 'Mass edit common product attributes & rules application', 'edit_common_attributes_with_permission_and_rules', 0, 'Akeneo Mass Edit Connector', 'a:3:{s:7:\"filters\";a:0:{}s:18:\"realTimeVersioning\";b:1;s:7:\"actions\";a:0:{}}', 'mass_edit'),
            ('add_to_variant_group_with_rules', 'Mass add products to variant group & rules application', 'add_to_variant_group_with_rules', 0, 'Akeneo Mass Edit Connector', 'a:3:{s:7:\"filters\";a:0:{}s:7:\"actions\";a:0:{}s:18:\"realTimeVersioning\";b:1;}', 'mass_edit');
SQL
        );

        $this->addSql(<<<SQL
            INSERT INTO pimee_security_job_profile_access
                (`job_profile_id`, `user_group_id`, `execute_job_profile`, `edit_job_profile`)
            SELECT j.id AS job_profile_id, g.id AS user_group_id, 1, 1
            FROM akeneo_batch_job_instance as j
            JOIN oro_access_group AS g ON g.name = "All"
            WHERE j.code IN (
                'update_product_value_with_permission_and_rules',
                'add_product_value_with_permission_and_rules',
                'edit_common_attributes_with_permission_and_rules',
                'add_to_variant_group_with_rules'
            );
SQL
        );
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $this->throwIrreversibleMigrationException();
    }
}
