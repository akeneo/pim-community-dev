<?php declare(strict_types=1);

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\Types\Types;
use Doctrine\Migrations\AbstractMigration;

/**
 * Add permission for the remove_non_existing_product_values job
 */
final class Version_4_0_20200803082132_remove_non_existing_values_job_permission extends AbstractMigration
{
    private const JOB_NAME = 'remove_non_existing_product_values';

    public function up(Schema $schema) : void
    {
        if ($this->jobProfileAccessIsAlreadyDefined()) {
            return;
        }

        $sql = <<<SQL
        INSERT INTO pimee_security_job_profile_access (job_profile_id, user_group_id, execute_job_profile, edit_job_profile)
            SELECT j.id AS job_profile_id, g.id AS user_group_id, 1, 1
            FROM akeneo_batch_job_instance as j
                JOIN oro_access_group AS g ON g.name = 'All'
            WHERE j.code = :code
        ;
SQL;

        $this->addSql($sql, ['code' => static::JOB_NAME]);

    }

    public function down(Schema $schema) : void
    {
        $this->throwIrreversibleMigrationException();
    }

    private function jobProfileAccessIsAlreadyDefined(): bool
    {
        $sql = <<<SQL
SELECT EXISTS (
    SELECT a.id
    FROM pimee_security_job_profile_access as a
        JOIN akeneo_batch_job_instance j ON j.id = a.job_profile_id
        JOIN oro_access_group g ON g.id = a.user_group_id
    WHERE j.code = :code AND g.name = 'All'
) AS is_existing
SQL;
        $result = $this->connection->executeQuery($sql, ['code' => static::JOB_NAME])->fetch(\PDO::FETCH_ASSOC);

        return Type::getType(Types::BOOLEAN)->convertToPhpValue(
            $result['is_existing'],
            $this->connection->getDatabasePlatform()
        );
    }
}
