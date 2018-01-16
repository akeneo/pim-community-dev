<?php

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * This migration script has been introduced in 2.1 and release in 2.1.
 * However the naming is not correct and the content has been moved to "Version_2_1_20180115172321_batch_jobs".
 *
 * We keep this migration file for users who have already played it to avoid showing them an error message saying that
 * one migration script already played cannot be found.
 */
class Version_2_0_171220162357 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
    }
}
