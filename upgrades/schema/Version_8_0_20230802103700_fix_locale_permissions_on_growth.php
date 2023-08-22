<?php

declare(strict_types=1);

namespace Pim\Upgrade\Schema;

use Akeneo\Platform\Bundle\PimVersionBundle\VersionProviderInterface;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class Version_8_0_20230802103700_fix_locale_permissions_on_growth extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('
            INSERT IGNORE INTO pimee_security_locale_access (locale_id, user_group_id, edit_products, view_products) (
                WITH access_groups as (
                    SELECT g.id FROM oro_access_group g
                    INNER JOIN akeneo_connectivity_connected_app a ON a.user_group_name = g.name
                    WHERE JSON_EXTRACT(default_permissions, "$.locale_view") = true 
                        AND JSON_EXTRACT(default_permissions, "$.locale_edit") = true
                ), enabled_locales as (
                    SELECT id FROM pim_catalog_locale 
                    WHERE is_activated=1
                )
                SELECT enabled_locales.id, access_groups.id, 1, 1 FROM enabled_locales CROSS JOIN access_groups
            );
        ');
    }

    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException();
    }
}
