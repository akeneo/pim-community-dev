<?php

declare(strict_types=1);

namespace AkeneoTest\Platform\Integration\ImportExport\Repository\InternalApi;

use Akeneo\Platform\Bundle\ImportExportBundle\Model\JobExecutionTracking;
use Akeneo\Platform\Bundle\ImportExportBundle\Model\StepExecutionTracking;
use Akeneo\Platform\Bundle\ImportExportBundle\Query\GetJobExecutionTracking;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Akeneo\Tool\Component\Batch\Job\BatchStatus;
use AkeneoTest\Platform\Integration\ImportExport\Utils\FrozenClock;
use Doctrine\DBAL\Connection;

class GetJobExecutionTrackingIntegration extends TestCase
{
    /** @var Connection */
    private $sqlConnection;

    /** @var GetJobExecutionTracking */
    private $getJobExecutionTracking;

    /** @var FrozenClock */
    private $clock;

    public function setUp(): void
    {
        parent::setUp();

        self::$container->set('pim_import_export.clock', new FrozenClock());

        $this->sqlConnection = $this->get('database_connection');
        $this->getJobExecutionTracking = $this->get('pim_import_export.query.get_job_execution_tracking');
        $this->clock = $this->get('pim_import_export.clock');
    }

    public function testItFetchesTheJobExecutionTrackingForAJobExecutionNotStarted(): void
    {
        $jobExecutionId = $this->thereIsAJobNotStarted();

        $jobExecutionTracking = $this->getJobExecutionTracking->execute($jobExecutionId);

        $expectedJobExecutionTracking = $this->expectedJobExecutionTrackingNotStarted();

        self::assertEquals($expectedJobExecutionTracking, $jobExecutionTracking);
    }

    public function testItFetchesTheJobExecutionTrackingForAJobExecutionInProgress(): void
    {
        $jobExecutionId = $this->thereIsAJobInProgress();

        $jobExecutionTracking = $this->getJobExecutionTracking->execute($jobExecutionId);

        $expectedJobExecutionTracking = $this->expectedJobExecutionTrackingInProgress();

        self::assertEquals($expectedJobExecutionTracking, $jobExecutionTracking);
    }

    public function testItFetchesTheJobExecutionTrackingForAJobExecutionTerminated(): void
    {
        $jobExecutionId = $this->thereIsAJobTerminated();

        $jobExecutionTracking = $this->getJobExecutionTracking->execute($jobExecutionId);

        $expectedJobExecutionTracking = $this->expectedJobExecutionTrackingTerminated();

        self::assertEquals($expectedJobExecutionTracking, $jobExecutionTracking);
    }

    public function testItFetchesTheJobExecutionTrackingForAJobExecutionFailed(): void
    {
        $jobExecutionId = $this->thereIsAJobFailed();

        $jobExecutionTracking = $this->getJobExecutionTracking->execute($jobExecutionId);

        $expectedJobExecutionTracking = $this->expectedJobExecutionTrackingFailed();

        self::assertEquals($expectedJobExecutionTracking, $jobExecutionTracking);
    }

    public function testItFetchesTheJobExecutionTrackingEvenIfJobDoesNotExistAnymore(): void
    {
        $jobExecutionId = $this->thereIsAJobExecutionOfJobThatDoesNotExistAnymore();

        $jobExecutionTracking = $this->getJobExecutionTracking->execute($jobExecutionId);

        $expectedJobExecutionTracking = $this->expectedJobExecutionTrackingOfJobThatDoesNotExistAnymore();

        self::assertEquals($expectedJobExecutionTracking, $jobExecutionTracking);
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useTechnicalCatalog();
    }

    private function thereIsAJobNotStarted(): int
    {
        $JobInstanceId = $this->sqlConnection->executeQuery('SELECT id FROM akeneo_batch_job_instance WHERE code = "csv_product_import";')->fetchColumn();
        $insertJobExecution = <<<SQL
INSERT INTO `akeneo_batch_job_execution` (job_instance_id, pid, user, status, start_time, end_time, create_time, updated_time, health_check_time, exit_code, exit_description, failure_exceptions, log_file, raw_parameters)
VALUES (:job_instance_id, null, 'admin', 2, null, null, '2020-10-16 09:38:16', null, null, 'UNKNOWN', '', 'a:0:{}', null, '{}');
SQL;
        $this->sqlConnection->executeUpdate($insertJobExecution, ['job_instance_id' => $JobInstanceId]);

        return (int)$this->sqlConnection->lastInsertId();
    }

    private function thereIsAJobTerminated(): int
    {
        $this->clock->setDateTime(new \DateTime('2020-10-13 14:06:02', new \DateTimeZone('UTC')));

        $JobInstanceId = $this->sqlConnection->executeQuery('SELECT id FROM akeneo_batch_job_instance WHERE code = "csv_product_import";')->fetchColumn();
        $insertJobExecution = <<<SQL
INSERT INTO `akeneo_batch_job_execution` (`job_instance_id`, `pid`, `user`, `status`, `start_time`, `end_time`, `create_time`, `updated_time`, `health_check_time`, `exit_code`, `exit_description`, `failure_exceptions`, `log_file`, `raw_parameters`)
VALUES
	(:job_instance_id, 86472, 'admin', 1, '2020-10-13 13:05:49', '2020-10-13 13:06:10', '2020-10-13 13:05:45', '2020-10-13 13:06:09', '2020-10-13 13:06:09', 'COMPLETED', '', 'a:0:{}', '', '{}');
SQL;
        $this->sqlConnection->executeUpdate($insertJobExecution, ['job_instance_id' => $JobInstanceId]);
        $jobExecutionId = (int)$this->sqlConnection->lastInsertId();

        $insertStepExecutions = <<<SQL
INSERT INTO `akeneo_batch_step_execution` (`job_execution_id`, `step_name`, `status`, `read_count`, `write_count`, `filter_count`, `start_time`, `end_time`, `exit_code`, `exit_description`, `terminate_only`, `failure_exceptions`, `errors`, `summary`, `tracking_data`)
VALUES
	(:job_execution_id, 'validation', 1, 0, 0, 0, '2020-10-13 13:05:50', '2020-10-13 13:05:55', 'COMPLETED', '', 0, 'a:0:{}', 'a:0:{}', 'a:1:{s:23:\"charset_validator.title\";s:8:\"UTF-8 OK\";}', '{"processedItems": 0, "totalItems": 0}'),
	(:job_execution_id, 'import', 1, 0, 0, 0, '2020-10-13 13:05:55', '2020-10-13 13:06:09', 'COMPLETED', '', 0, 'a:0:{}', 'a:0:{}', 'a:3:{s:13:\"item_position\";i:38;s:23:\"product_skipped_no_diff\";i:37;s:4:\"skip\";i:1;}', '{"processedItems": 10, "totalItems": 100}'),
	(:job_execution_id, 'import_associations', 1, 0, 0, 0, '2020-10-13 13:06:09', '2020-10-13 13:06:10', 'COMPLETED', '', 0, 'a:0:{}', 'a:0:{}', 'a:0:{}', '{"processedItems": 0, "totalItems": 0}')
;
SQL;
        $this->sqlConnection->executeUpdate($insertStepExecutions, ['job_execution_id' => $jobExecutionId]);

        return $jobExecutionId;
    }

    private function thereIsAJobInProgress(): int
    {
        $this->clock->setDateTime(new \DateTime('2020-10-13 13:06:02', new \DateTimeZone('UTC')));

        $JobInstanceId = $this->sqlConnection->executeQuery('SELECT id FROM akeneo_batch_job_instance WHERE code = "csv_product_import";')->fetchColumn();
        $insertJobExecution = <<<SQL
INSERT INTO `akeneo_batch_job_execution` (`job_instance_id`, `pid`, `user`, `status`, `start_time`, `end_time`, `create_time`, `updated_time`, `health_check_time`, `exit_code`, `exit_description`, `failure_exceptions`, `log_file`, `raw_parameters`)
VALUES
	(:job_instance_id, 86472, 'admin', 3, '2020-10-13 13:05:49', '2020-10-13 13:05:49', '2020-10-13 13:05:45', '2020-10-13 13:05:48', '2020-10-13 13:05:48', 'STARTED', '', 'a:0:{}', '', '{}');
SQL;
        $this->sqlConnection->executeUpdate($insertJobExecution, ['job_instance_id' => $JobInstanceId]);
        $jobExecutionId = (int)$this->sqlConnection->lastInsertId();

        $insertStepExecutions = <<<SQL
INSERT INTO `akeneo_batch_step_execution` (`job_execution_id`, `step_name`, `status`, `read_count`, `write_count`, `filter_count`, `start_time`, `end_time`, `exit_code`, `exit_description`, `terminate_only`, `failure_exceptions`, `errors`, `summary`, `tracking_data`)
VALUES
	(:job_execution_id, 'validation', 1, 0, 0, 0, '2020-10-13 13:05:50', '2020-10-13 13:05:55', 'COMPLETED', '', 0, 'a:0:{}', 'a:0:{}', 'a:1:{s:23:\"charset_validator.title\";s:8:\"UTF-8 OK\";}', '{"processedItems": 0, "totalItems": 0}'),
	(:job_execution_id, 'import', 3, 0, 0, 0, '2020-10-13 13:05:55', null, 'STARTED', '', 0, 'a:0:{}', 'a:0:{}', 'a:3:{s:13:\"item_position\";i:38;s:23:\"product_skipped_no_diff\";i:37;s:4:\"skip\";i:1;}', '{"processedItems": 10, "totalItems": 100}')
	;
SQL;
        $this->sqlConnection->executeUpdate($insertStepExecutions, ['job_execution_id' => $jobExecutionId]);
        $stepExecutionId = $this->sqlConnection->lastInsertId();

        $insertWarnings = <<<SQL
INSERT INTO `akeneo_batch_warning` (`step_execution_id`, `reason`, `reason_parameters`, `item`)
VALUES
	(:step_execution_id, 'Property \"variation_image\" expects a valid pathname as data, \"/var/folders/jm/d58y_3x52v9dz79knt487byh0000gp/T/akeneo_batch_5f85a62d0a7c5//files/Tshirt-unique-size-blue/variation_image/unique-size.jpg\" given.', 'a:0:{}', 'a:7:{s:10:\"categories\";a:1:{i:0;s:7:\"tshirts\";}s:7:\"enabled\";b:1;s:6:\"family\";s:8:\"clothing\";s:6:\"parent\";s:24:\"model-tshirt-unique-size\";s:6:\"groups\";a:0:{}s:6:\"values\";a:6:{s:3:\"sku\";a:1:{i:0;a:3:{s:6:\"locale\";N;s:5:\"scope\";N;s:4:\"data\";s:23:\"Tshirt-unique-size-blue\";}}s:5:\"color\";a:1:{i:0;a:3:{s:6:\"locale\";N;s:5:\"scope\";N;s:4:\"data\";s:4:\"blue\";}}s:11:\"composition\";a:1:{i:0;a:3:{s:6:\"locale\";N;s:5:\"scope\";N;s:4:\"data\";N;}}s:3:\"ean\";a:1:{i:0;a:3:{s:6:\"locale\";N;s:5:\"scope\";N;s:4:\"data\";s:13:\"1234567890350\";}}s:15:\"variation_image\";a:1:{i:0;a:3:{s:6:\"locale\";N;s:5:\"scope\";N;s:4:\"data\";s:138:\"/var/folders/jm/d58y_3x52v9dz79knt487byh0000gp/T/akeneo_batch_5f85a62d0a7c5//files/Tshirt-unique-size-blue/variation_image/unique-size.jpg\";}}s:14:\"variation_name\";a:3:{i:0;a:3:{s:6:\"locale\";s:5:\"de_DE\";s:5:\"scope\";N;s:4:\"data\";N;}i:1;a:3:{s:6:\"locale\";s:5:\"en_US\";s:5:\"scope\";N;s:4:\"data\";s:24:\"T-shirt unique size blue\";}i:2;a:3:{s:6:\"locale\";s:5:\"fr_FR\";s:5:\"scope\";N;s:4:\"data\";N;}}}s:10:\"identifier\";s:23:\"Tshirt-unique-size-blue\";}')
	;
SQL;
        $this->sqlConnection->executeUpdate($insertWarnings, ['step_execution_id' => $stepExecutionId]);

        return $jobExecutionId;
    }

    private function thereIsAJobFailed(): int
    {
        $this->clock->setDateTime(new \DateTime('2020-10-13 13:06:02', new \DateTimeZone('UTC')));

        $JobInstanceId = $this->sqlConnection->executeQuery('SELECT id FROM akeneo_batch_job_instance WHERE code = "csv_product_import";')->fetchColumn();
        $insertJobExecution = <<<SQL
INSERT INTO akeneo_batch_job_execution (job_instance_id, pid, user, status, start_time, end_time, create_time, updated_time, health_check_time, exit_code, exit_description, failure_exceptions, log_file, raw_parameters)
VALUES (:job_instance_id, 55, 'admin', 6, '2020-10-16 09:50:28', '2020-10-16 09:50:29', '2020-10-16 09:50:26', '2020-10-16 09:50:28', '2020-10-16 09:50:28', 'FAILED', 'une backtrace', 'a:0:{}', '/srv/pim/var/logs/batch/26/batch_753d665999a008628d64a94e0ae83a52cc8f7d87.log', '{}');
SQL;

        $this->sqlConnection->executeUpdate($insertJobExecution, ['job_instance_id' => $JobInstanceId]);
        $jobExecutionId = (int)$this->sqlConnection->lastInsertId();

        $insertStepExecutions = <<<SQL
INSERT INTO akeneo_batch_step_execution (job_execution_id, step_name, status, read_count, write_count, filter_count, start_time, end_time, exit_code, exit_description, terminate_only, failure_exceptions, errors, summary, tracking_data)
VALUES
    (:job_execution_id, 'validation', 1, 0, 0, 0, '2020-10-16 09:50:28', '2020-10-16 09:50:33', 'COMPLETED', '', 0, 'a:0:{}', 'a:0:{}', 'a:1:{s:23:"charset_validator.title";s:8:"UTF-8 OK";}', '{"totalItems": 0, "processedItems": 0}'),
    (:job_execution_id, 'import', 6, 0, 0, 0, '2020-10-16 09:50:28', '2020-10-16 09:50:42', 'FAILED', 'une backtrace', 0, 'a:1:{s:5:"error";s:12:"an backtrace";}', 'a:0:{}', 'a:1:{s:13:"item_position";i:1;}', '{"totalItems": 100, "processedItems": 10}');
SQL;

        $this->sqlConnection->executeUpdate($insertStepExecutions, ['job_execution_id' => $jobExecutionId]);

        return $jobExecutionId;
    }

    private function thereIsAJobExecutionOfJobThatDoesNotExistAnymore(): int
    {
        $this->clock->setDateTime(new \DateTime('2020-10-13 13:06:02', new \DateTimeZone('UTC')));

        $this->sqlConnection->executeUpdate("INSERT INTO akeneo_batch_job_instance(code, label, job_name, status, connector, raw_parameters, type) VALUES ('my_custom_job_deleted', 'My custom job deleted', 'my_custom_job_deleted', 0, 'My connector', 'a:0:{}', 'export');");
        $jobInstanceId = (int)$this->sqlConnection->lastInsertId();

        $insertJobExecution = <<<SQL
INSERT INTO akeneo_batch_job_execution (job_instance_id, pid, user, status, start_time, end_time, create_time, updated_time, health_check_time, exit_code, exit_description, failure_exceptions, log_file, raw_parameters)
VALUES (:job_instance_id, 55, 'admin', 1, '2020-10-16 09:50:28', '2020-10-16 09:50:42', '2020-10-16 09:50:26', '2020-10-16 09:50:42', '2020-10-16 09:50:42', 'COMPLETED', '', 'a:0:{}', '/srv/pim/var/logs/batch/26/batch_753d665999a008628d64a94e0ae83a52cc8f7d87.log', '{}');
SQL;

        $this->sqlConnection->executeUpdate($insertJobExecution, ['job_instance_id' => $jobInstanceId]);
        $jobExecutionId = (int)$this->sqlConnection->lastInsertId();

        $insertStepExecutions = <<<SQL
INSERT INTO akeneo_batch_step_execution (job_execution_id, step_name, status, read_count, write_count, filter_count, start_time, end_time, exit_code, exit_description, terminate_only, failure_exceptions, errors, summary, tracking_data)
VALUES
    (:job_execution_id, 'first_step', 1, 0, 0, 0, '2020-10-16 09:50:28', '2020-10-16 09:50:33', 'COMPLETED', '', 0, 'a:0:{}', 'a:0:{}', 'a:1:{s:23:"charset_validator.title";s:8:"UTF-8 OK";}', '{"totalItems": 0, "processedItems": 0}'),
    (:job_execution_id, 'second_step', 1, 0, 0, 0, '2020-10-16 09:50:33', '2020-10-16 09:50:42', 'COMPLETED', '', 0, 'a:0:{}', 'a:0:{}', 'a:1:{s:13:"item_position";i:1;}', '{"totalItems": 100, "processedItems": 10}');
SQL;

        $this->sqlConnection->executeUpdate($insertStepExecutions, ['job_execution_id' => $jobExecutionId]);

        return $jobExecutionId;
    }

    private function expectedJobExecutionTrackingNotStarted(): JobExecutionTracking
    {
        $expectedJobExecutionTracking = new JobExecutionTracking();
        $expectedJobExecutionTracking->status = 'STARTING';
        $expectedJobExecutionTracking->currentStep = 0;
        $expectedJobExecutionTracking->totalSteps = 3;

        $expectedJobExecutionTracking->steps = [
            $this->getValidationStepExecutionTracking(),
            $this->getImportStepExecutionTracking(),
            $this->getImportAssociationsStepExecutionTracking()
        ];

        return $expectedJobExecutionTracking;
    }

    private function expectedJobExecutionTrackingInProgress(): JobExecutionTracking
    {
        $expectedJobExecutionTracking = new JobExecutionTracking();
        $expectedJobExecutionTracking->status = 'STARTED';
        $expectedJobExecutionTracking->currentStep = 2;
        $expectedJobExecutionTracking->totalSteps = 3;

        $expectedStepExecutionTracking1 = $this->getValidationStepExecutionTracking();
        $expectedStepExecutionTracking1->status = 'COMPLETED';
        $expectedStepExecutionTracking1->duration = 5;
        $expectedStepExecutionTracking1->hasWarning = true;

        $expectedStepExecutionTracking2 = $this->getImportStepExecutionTracking();
        $expectedStepExecutionTracking2->status = 'STARTED';
        $expectedStepExecutionTracking2->duration = 7;
        $expectedStepExecutionTracking2->processedItems = 10;
        $expectedStepExecutionTracking2->totalItems = 100;

        $expectedStepExecutionTracking3 = $this->getImportAssociationsStepExecutionTracking();

        $expectedJobExecutionTracking->steps = [
            $expectedStepExecutionTracking1,
            $expectedStepExecutionTracking2,
            $expectedStepExecutionTracking3
        ];

        return $expectedJobExecutionTracking;
    }

    private function expectedJobExecutionTrackingTerminated(): JobExecutionTracking
    {
        $expectedJobExecutionTracking = new JobExecutionTracking();
        $expectedJobExecutionTracking->status = 'COMPLETED';
        $expectedJobExecutionTracking->currentStep = 3;
        $expectedJobExecutionTracking->totalSteps = 3;

        $expectedStepExecutionTracking1 = $this->getValidationStepExecutionTracking();
        $expectedStepExecutionTracking1->status = 'COMPLETED';
        $expectedStepExecutionTracking1->duration = 5;

        $expectedStepExecutionTracking2 = $this->getImportStepExecutionTracking();
        $expectedStepExecutionTracking2->status = 'COMPLETED';
        $expectedStepExecutionTracking2->duration = 14;
        $expectedStepExecutionTracking2->processedItems = 10;
        $expectedStepExecutionTracking2->totalItems = 100;

        $expectedStepExecutionTracking3 = $this->getImportAssociationsStepExecutionTracking();
        $expectedStepExecutionTracking3->status = 'COMPLETED';
        $expectedStepExecutionTracking3->duration = 1;

        $expectedJobExecutionTracking->steps = [
            $expectedStepExecutionTracking1,
            $expectedStepExecutionTracking2,
            $expectedStepExecutionTracking3
        ];

        return $expectedJobExecutionTracking;
    }

    private function expectedJobExecutionTrackingFailed(): JobExecutionTracking
    {
        $expectedJobExecutionTracking = new JobExecutionTracking();
        $expectedJobExecutionTracking->status = 'FAILED';
        $expectedJobExecutionTracking->currentStep = 2;
        $expectedJobExecutionTracking->totalSteps = 3;

        $expectedStepExecutionTracking1 = $this->getValidationStepExecutionTracking();
        $expectedStepExecutionTracking1->status = 'COMPLETED';
        $expectedStepExecutionTracking1->duration = 5;

        $expectedStepExecutionTracking2 = $this->getImportStepExecutionTracking();
        $expectedStepExecutionTracking2->status = 'FAILED';
        $expectedStepExecutionTracking2->duration = 14;
        $expectedStepExecutionTracking2->hasError = true;
        $expectedStepExecutionTracking2->processedItems = 10;
        $expectedStepExecutionTracking2->totalItems = 100;

        $expectedStepExecutionTracking3 = $this->getImportAssociationsStepExecutionTracking();
        $expectedStepExecutionTracking3->status = 'STARTING';
        $expectedStepExecutionTracking3->duration = 0;

        $expectedJobExecutionTracking->steps = [
            $expectedStepExecutionTracking1,
            $expectedStepExecutionTracking2,
            $expectedStepExecutionTracking3
        ];

        return $expectedJobExecutionTracking;
    }

    private function expectedJobExecutionTrackingOfJobThatDoesNotExistAnymore(): JobExecutionTracking
    {
        $expectedJobExecutionTracking = new JobExecutionTracking();
        $expectedJobExecutionTracking->status = 'COMPLETED';
        $expectedJobExecutionTracking->currentStep = 2;
        $expectedJobExecutionTracking->totalSteps = 2;

        $stepExecutionTracking1 = new StepExecutionTracking();
        $stepExecutionTracking1->jobName = 'my_custom_job_deleted';
        $stepExecutionTracking1->stepName = 'first_step';
        $stepExecutionTracking1->status = 'COMPLETED';
        $stepExecutionTracking1->duration = 5;
        $stepExecutionTracking1->isTrackable = false;

        $stepExecutionTracking2 = new StepExecutionTracking();
        $stepExecutionTracking2->jobName = 'my_custom_job_deleted';
        $stepExecutionTracking2->stepName = 'second_step';
        $stepExecutionTracking2->status = 'COMPLETED';
        $stepExecutionTracking2->duration = 9;
        $stepExecutionTracking2->isTrackable = false;

        $expectedJobExecutionTracking->steps = [
            $stepExecutionTracking1,
            $stepExecutionTracking2,
        ];

        return $expectedJobExecutionTracking;
    }

    private function getValidationStepExecutionTracking(): StepExecutionTracking
    {
        $stepExecutionTracking = new StepExecutionTracking();
        $stepExecutionTracking->isTrackable = false;
        $stepExecutionTracking->jobName = 'csv_product_import';
        $stepExecutionTracking->stepName = 'validation';
        $stepExecutionTracking->status = 'STARTING';
        $stepExecutionTracking->duration = 0;
        $stepExecutionTracking->hasError = false;
        $stepExecutionTracking->hasWarning = false;
        $stepExecutionTracking->processedItems = 0;
        $stepExecutionTracking->totalItems = 0;

        return $stepExecutionTracking;
    }

    private function getImportStepExecutionTracking(): StepExecutionTracking
    {
        $stepExecutionTracking = new StepExecutionTracking();
        $stepExecutionTracking->isTrackable = true;
        $stepExecutionTracking->jobName = 'csv_product_import';
        $stepExecutionTracking->stepName = 'import';
        $stepExecutionTracking->status = 'STARTING';
        $stepExecutionTracking->duration = 0;
        $stepExecutionTracking->hasError = false;
        $stepExecutionTracking->hasWarning = false;
        $stepExecutionTracking->processedItems = 0;
        $stepExecutionTracking->totalItems = 0;

        return $stepExecutionTracking;
    }

    private function getImportAssociationsStepExecutionTracking(): StepExecutionTracking
    {
        $stepExecutionTracking = new StepExecutionTracking();
        $stepExecutionTracking->isTrackable = true;
        $stepExecutionTracking->jobName = 'csv_product_import';
        $stepExecutionTracking->stepName = 'import_associations';
        $stepExecutionTracking->status = 'STARTING';
        $stepExecutionTracking->duration = 0;
        $stepExecutionTracking->hasError = false;
        $stepExecutionTracking->hasWarning = false;
        $stepExecutionTracking->processedItems = 0;
        $stepExecutionTracking->totalItems = 0;

        return $stepExecutionTracking;
    }
}
