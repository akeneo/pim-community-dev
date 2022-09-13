<?php

declare(strict_types=1);

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Pim\Upgrade\test_schema;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Doctrine\DBAL\Connection;
use Pim\Upgrade\Schema\Tests\ExecuteMigrationTrait;

final class Version_7_0_20220912154800_change_html_clean_operation_Integration extends TestCase
{
    use ExecuteMigrationTrait;

    private const MIGRATION_LABEL = '_7_0_20220912154800_change_html_clean_operation';

    private Connection $connection;

    protected function setUp(): void
    {
        parent::setUp();
        $this->connection = $this->get('database_connection');
    }

    public function it_change_clean_html_tags_operation_in_to_clean_html_operation(): void
    {
        $this->writeJobInstance();
        $this->reExecuteMigration(self::MIGRATION_LABEL);
        $job = $this->getJob();

        $this->assertEquals('a:10:{s:7:"storage";a:1:{s:4:"type";s:4:"none";}s:10:"withHeader";b:1;s:13:"uploadAllowed";b:1;s:25:"invalid_items_file_format";s:4:"xlsx";s:15:"users_to_notify";a:0:{}s:21:"is_user_authenticated";b:0;s:12:"error_action";s:12:"skip_product";s:16:"import_structure";a:2:{s:7:"columns";a:5:{i:0;a:3:{s:4:"uuid";s:36:"fb128222-3368-482c-a496-c768d884c47a";s:5:"index";i:0;s:5:"label";s:3:"sku";}i:1;a:3:{s:4:"uuid";s:36:"bbac7dad-8d1c-4ae3-a2ec-e0cb952a7bdf";s:5:"index";i:1;s:5:"label";s:6:"family";}i:2;a:3:{s:4:"uuid";s:36:"920c9945-5caf-4a29-9bca-be4f5bfc35be";s:5:"index";i:2;s:5:"label";s:4:"code";}i:3;a:3:{s:4:"uuid";s:36:"ccd0ec0b-67d9-426d-8351-6b00f11083a1";s:5:"index";i:3;s:5:"label";s:6:"select";}i:4;a:3:{s:4:"uuid";s:36:"6d305b32-91ab-49cf-806d-3b9df21b50b0";s:5:"index";i:4;s:5:"label";s:11:"description";}}s:13:"data_mappings";a:2:{i:0;a:5:{s:4:"uuid";s:36:"b65a75df-39f2-4d5e-9bb1-3d458ed35aa9";s:6:"target";a:8:{s:4:"code";s:3:"sku";s:4:"type";s:9:"attribute";s:14:"attribute_type";s:22:"pim_catalog_identifier";s:6:"locale";N;s:7:"channel";N;s:20:"source_configuration";N;s:19:"action_if_not_empty";s:3:"set";s:15:"action_if_empty";s:4:"skip";}s:7:"sources";a:1:{i:0;s:36:"fb128222-3368-482c-a496-c768d884c47a";}s:10:"operations";a:0:{}s:11:"sample_data";a:3:{i:0;s:36:"83f85e1d-0ca2-4513-a4b2-af63eeeb6058";i:1;s:36:"cad06b3d-e5b2-4df2-bfc1-de538070ac53";i:2;s:36:"f553c848-a34c-44e4-b381-a6243f2df3f6";}}i:1;a:5:{s:4:"uuid";s:36:"7093e1f2-59ea-4b46-ae4c-7eacdb680aa0";s:6:"target";a:8:{s:4:"code";s:11:"description";s:4:"type";s:9:"attribute";s:14:"attribute_type";s:20:"pim_catalog_textarea";s:6:"locale";s:5:"de_DE";s:7:"channel";s:9:"ecommerce";s:20:"source_configuration";N;s:19:"action_if_not_empty";s:3:"set";s:15:"action_if_empty";s:4:"skip";}s:7:"sources";a:1:{i:0;s:36:"6d305b32-91ab-49cf-806d-3b9df21b50b0";}s:10:"operations";a:1:{i:0;a:3:{s:4:"uuid";s:36:"19d3f1c5-366e-442e-be3c-490736730b94";s:4:"type";s:10:"clean_html";s:5:"modes";a:2:{i:0;s:6:"remove";i:1;s:6:"decode";}}}s:11:"sample_data";a:3:{i:0;s:101:"<p><b>L</b>or&eacute;m ipsum&nbsp;dolor sit am&eacute;t, consectetur adipiscing elit.<br/> Fusce arcu";i:1;s:101:"<p><b>L</b>or&eacute;m ipsum dolor sit am&eacute;t, consectetur adipiscing elit.<br/> Fusce arcu urna";i:2;N;}}}}s:8:"file_key";s:58:"7/8/f/8/78f8325506b2589e5ec5be20ce9bab1eb50be69e_demo.xlsx";s:14:"file_structure";a:5:{s:10:"header_row";i:1;s:12:"first_column";i:0;s:17:"first_product_row";i:2;s:10:"sheet_name";s:6:"Sheet1";s:24:"unique_identifier_column";i:0;}}', $job['raw_parameters']);

        $this->reExecuteMigration(self::MIGRATION_LABEL);
        $job = $this->getJob();

        $this->assertEquals('a:10:{s:7:"storage";a:1:{s:4:"type";s:4:"none";}s:10:"withHeader";b:1;s:13:"uploadAllowed";b:1;s:25:"invalid_items_file_format";s:4:"xlsx";s:15:"users_to_notify";a:0:{}s:21:"is_user_authenticated";b:0;s:12:"error_action";s:12:"skip_product";s:16:"import_structure";a:2:{s:7:"columns";a:5:{i:0;a:3:{s:4:"uuid";s:36:"fb128222-3368-482c-a496-c768d884c47a";s:5:"index";i:0;s:5:"label";s:3:"sku";}i:1;a:3:{s:4:"uuid";s:36:"bbac7dad-8d1c-4ae3-a2ec-e0cb952a7bdf";s:5:"index";i:1;s:5:"label";s:6:"family";}i:2;a:3:{s:4:"uuid";s:36:"920c9945-5caf-4a29-9bca-be4f5bfc35be";s:5:"index";i:2;s:5:"label";s:4:"code";}i:3;a:3:{s:4:"uuid";s:36:"ccd0ec0b-67d9-426d-8351-6b00f11083a1";s:5:"index";i:3;s:5:"label";s:6:"select";}i:4;a:3:{s:4:"uuid";s:36:"6d305b32-91ab-49cf-806d-3b9df21b50b0";s:5:"index";i:4;s:5:"label";s:11:"description";}}s:13:"data_mappings";a:2:{i:0;a:5:{s:4:"uuid";s:36:"b65a75df-39f2-4d5e-9bb1-3d458ed35aa9";s:6:"target";a:8:{s:4:"code";s:3:"sku";s:4:"type";s:9:"attribute";s:14:"attribute_type";s:22:"pim_catalog_identifier";s:6:"locale";N;s:7:"channel";N;s:20:"source_configuration";N;s:19:"action_if_not_empty";s:3:"set";s:15:"action_if_empty";s:4:"skip";}s:7:"sources";a:1:{i:0;s:36:"fb128222-3368-482c-a496-c768d884c47a";}s:10:"operations";a:0:{}s:11:"sample_data";a:3:{i:0;s:36:"83f85e1d-0ca2-4513-a4b2-af63eeeb6058";i:1;s:36:"cad06b3d-e5b2-4df2-bfc1-de538070ac53";i:2;s:36:"f553c848-a34c-44e4-b381-a6243f2df3f6";}}i:1;a:5:{s:4:"uuid";s:36:"7093e1f2-59ea-4b46-ae4c-7eacdb680aa0";s:6:"target";a:8:{s:4:"code";s:11:"description";s:4:"type";s:9:"attribute";s:14:"attribute_type";s:20:"pim_catalog_textarea";s:6:"locale";s:5:"de_DE";s:7:"channel";s:9:"ecommerce";s:20:"source_configuration";N;s:19:"action_if_not_empty";s:3:"set";s:15:"action_if_empty";s:4:"skip";}s:7:"sources";a:1:{i:0;s:36:"6d305b32-91ab-49cf-806d-3b9df21b50b0";}s:10:"operations";a:1:{i:0;a:3:{s:4:"uuid";s:36:"19d3f1c5-366e-442e-be3c-490736730b94";s:4:"type";s:10:"clean_html";s:5:"modes";a:2:{i:0;s:6:"remove";i:1;s:6:"decode";}}}s:11:"sample_data";a:3:{i:0;s:101:"<p><b>L</b>or&eacute;m ipsum&nbsp;dolor sit am&eacute;t, consectetur adipiscing elit.<br/> Fusce arcu";i:1;s:101:"<p><b>L</b>or&eacute;m ipsum dolor sit am&eacute;t, consectetur adipiscing elit.<br/> Fusce arcu urna";i:2;N;}}}}s:8:"file_key";s:58:"7/8/f/8/78f8325506b2589e5ec5be20ce9bab1eb50be69e_demo.xlsx";s:14:"file_structure";a:5:{s:10:"header_row";i:1;s:12:"first_column";i:0;s:17:"first_product_row";i:2;s:10:"sheet_name";s:6:"Sheet1";s:24:"unique_identifier_column";i:0;}}', $job['raw_parameters']);
    }

    public function writeJobInstance()
    {
        $sql = <<<SQL
            INSERT INTO akeneo_batch_job_instance (code, label, job_name, status, connector, raw_parameters, automation, type) 
            VALUES (:values);
        SQL;

        $job = [
            'code' => 'tailored_import',
            'label' => 'tailored import',
            'job_name' => 'xlsx_tailored_import',
            'status' => 0,
            'connector' => 'Akeneo Tailored Import',
            'raw_parameters' => 'a:10:{s:7:"storage";a:1:{s:4:"type";s:4:"none";}s:10:"withHeader";b:1;s:13:"uploadAllowed";b:1;s:25:"invalid_items_file_format";s:4:"xlsx";s:15:"users_to_notify";a:0:{}s:21:"is_user_authenticated";b:0;s:12:"error_action";s:12:"skip_product";s:16:"import_structure";a:2:{s:7:"columns";a:5:{i:0;a:3:{s:4:"uuid";s:36:"fb128222-3368-482c-a496-c768d884c47a";s:5:"index";i:0;s:5:"label";s:3:"sku";}i:1;a:3:{s:4:"uuid";s:36:"bbac7dad-8d1c-4ae3-a2ec-e0cb952a7bdf";s:5:"index";i:1;s:5:"label";s:6:"family";}i:2;a:3:{s:4:"uuid";s:36:"920c9945-5caf-4a29-9bca-be4f5bfc35be";s:5:"index";i:2;s:5:"label";s:4:"code";}i:3;a:3:{s:4:"uuid";s:36:"ccd0ec0b-67d9-426d-8351-6b00f11083a1";s:5:"index";i:3;s:5:"label";s:6:"select";}i:4;a:3:{s:4:"uuid";s:36:"6d305b32-91ab-49cf-806d-3b9df21b50b0";s:5:"index";i:4;s:5:"label";s:11:"description";}}s:13:"data_mappings";a:2:{i:0;a:5:{s:4:"uuid";s:36:"b65a75df-39f2-4d5e-9bb1-3d458ed35aa9";s:6:"target";a:8:{s:4:"code";s:3:"sku";s:4:"type";s:9:"attribute";s:14:"attribute_type";s:22:"pim_catalog_identifier";s:6:"locale";N;s:7:"channel";N;s:20:"source_configuration";N;s:19:"action_if_not_empty";s:3:"set";s:15:"action_if_empty";s:4:"skip";}s:7:"sources";a:1:{i:0;s:36:"fb128222-3368-482c-a496-c768d884c47a";}s:10:"operations";a:0:{}s:11:"sample_data";a:3:{i:0;s:36:"83f85e1d-0ca2-4513-a4b2-af63eeeb6058";i:1;s:36:"cad06b3d-e5b2-4df2-bfc1-de538070ac53";i:2;s:36:"f553c848-a34c-44e4-b381-a6243f2df3f6";}}i:1;a:5:{s:4:"uuid";s:36:"7093e1f2-59ea-4b46-ae4c-7eacdb680aa0";s:6:"target";a:8:{s:4:"code";s:11:"description";s:4:"type";s:9:"attribute";s:14:"attribute_type";s:20:"pim_catalog_textarea";s:6:"locale";s:5:"de_DE";s:7:"channel";s:9:"ecommerce";s:20:"source_configuration";N;s:19:"action_if_not_empty";s:3:"set";s:15:"action_if_empty";s:4:"skip";}s:7:"sources";a:1:{i:0;s:36:"6d305b32-91ab-49cf-806d-3b9df21b50b0";}s:10:"operations";a:1:{i:0;a:2:{s:4:"uuid";s:36:"19d3f1c5-366e-442e-be3c-490736730b94";s:4:"type";s:15:"clean_html_tags";}}s:11:"sample_data";a:3:{i:0;s:101:"<p><b>L</b>or&eacute;m ipsum&nbsp;dolor sit am&eacute;t, consectetur adipiscing elit.<br/> Fusce arcu";i:1;s:101:"<p><b>L</b>or&eacute;m ipsum dolor sit am&eacute;t, consectetur adipiscing elit.<br/> Fusce arcu urna";i:2;N;}}}}s:8:"file_key";s:58:"7/8/f/8/78f8325506b2589e5ec5be20ce9bab1eb50be69e_demo.xlsx";s:14:"file_structure";a:5:{s:10:"header_row";i:1;s:12:"first_column";i:0;s:17:"first_product_row";i:2;s:10:"sheet_name";s:6:"Sheet1";s:24:"unique_identifier_column";i:0;}}',
            'type' => 'import'
        ];

        $this->connection->executeQuery($sql, $job)->execute();
    }

    public function getJob()
    {
        $sql = <<<SQL
            SELECT raw_parameters FROM akeneo_batch_job_instance WHERE connector = 'Akeneo Tailored Import';
        SQL;

        return $this->connection->executeQuery($sql)->fetchAllAssociative();
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }
}
