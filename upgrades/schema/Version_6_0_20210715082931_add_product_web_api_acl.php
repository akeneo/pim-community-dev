<?php

declare(strict_types=1);

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class Version_6_0_20210715082931_add_product_web_api_acl extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // This migration has been disabled, it was using services and was, in consequence, not future-proof.
        // The initial intention of this migration was done in a newer migration, sql only.
        $this->addSql('SELECT 1');
    }

    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException();
    }
}
