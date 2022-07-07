<?php

declare(strict_types=1);

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version_7_0_20220421150300_onboarder_serenity_add_supplier_import_job extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        if ($this->onboarderSerenityXlsxSupplierImportJobExists()) {
            return;
        }

        $sql = <<<SQL
            INSERT INTO akeneo_batch_job_instance (code, label, job_name, status, connector, raw_parameters, type)
            VALUES (:code, :label, :code, 0, :connector, :rawParameters, :type);
        SQL;

        $this->addSql(
            $sql,
            [
                'code' => 'supplier_portal_xlsx_supplier_import',
                'label' => 'Supplier Portal XLSX Supplier Import',
                'connector' => 'Supplier Portal',
                'rawParameters' => 'a:0:{}',
                'type' => 'import',
            ]
        );
    }

    public function down(Schema $schema) : void
    {
        $this->throwIrreversibleMigrationException();
    }

    private function onboarderSerenityXlsxSupplierImportJobExists(): bool
    {
        $sql = <<<SQL
            SELECT COUNT(*)
            FROM `akeneo_batch_job_instance`
            WHERE code = :code
        SQL;

        return 1 === (int) $this
                ->connection
                ->executeQuery(
                    $sql,
                    ['code' => 'supplier_portal_xlsx_supplier_import']
                )
                ->fetchOne()
            ;
    }
}
