<?php

declare(strict_types=1);

namespace Akeneo\Test\Integration\integration\BatchBundle\Command;

use Akeneo\Tool\Component\Batch\Job\BatchStatus;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Doctrine\DBAL\Driver\Connection;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;

class BatchCommandIntegration extends TestCase
{
    const EXPORT_DIRECTORY = 'pim-integration-tests-export';

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->createProduct('product_1');
        $this->createProduct('product_2');
    }

    public function testJobExecutionStateWhenJobIsCompleted()
    {
        $output = $this->launchJob();
        $jobExecution = $this->getJobExecution();

        $this->assertEquals(BatchStatus::COMPLETED, $jobExecution['status']);
        $this->assertNotNull($jobExecution['start_time']);
        $this->assertNotNull($jobExecution['end_time']);
        $this->assertNotNull($jobExecution['create_time']);
        $this->assertNotNull($jobExecution['pid']);
        $this->assertNotNull($jobExecution['log_file']);
        $this->assertNotNull(json_decode($jobExecution['raw_parameters'], true));
        $this->assertNull($jobExecution['user']);
        $this->assertEquals('Export csv_product_export has been successfully executed.' . PHP_EOL, $output->fetch());
    }

    public function testJobExecutionStateWithUsername()
    {
        $output = $this->launchJob(['--username' => 'mary']);
        $jobExecution = $this->getJobExecution();

        $this->assertEquals(BatchStatus::COMPLETED, $jobExecution['status']);
        $this->assertNotNull($jobExecution['start_time']);
        $this->assertNotNull($jobExecution['end_time']);
        $this->assertNotNull($jobExecution['create_time']);
        $this->assertNotNull($jobExecution['pid']);
        $this->assertNotNull($jobExecution['log_file']);
        $this->assertNotNull(json_decode($jobExecution['raw_parameters'], true));
        $this->assertEquals('mary', $jobExecution['user']);
        $this->assertEquals('Export csv_product_export has been successfully executed.' . PHP_EOL, $output->fetch());
    }

    public function testLaunchJobWithConfigOverridden()
    {
        $filePath= sys_get_temp_dir() . DIRECTORY_SEPARATOR . self::EXPORT_DIRECTORY . DIRECTORY_SEPARATOR . 'new_export.csv';
        if (file_exists($filePath)) {
            unlink($filePath);
        }

        $output = $this->launchJob(['--config' => ['filePath' => $filePath]]);
        $this->assertEquals('Export csv_product_export has been successfully executed.' . PHP_EOL, $output->fetch());
        $this->assertTrue(file_exists($filePath));
    }

    public function testLaunchJobWithNoLog()
    {
        $output = $this->launchJob(['--no-log' => true]);
        $this->assertEquals('Export csv_product_export has been successfully executed.' . PHP_EOL, $output->fetch());
    }

    public function testLaunchJobWithNormalVerbosity()
    {
        $output = $this->launchJob(['--no-log' => false]);
        $this->assertEquals('Export csv_product_export has been successfully executed.' . PHP_EOL, $output->fetch());
    }

    public function testLaunchJobWithDebugVerbosity()
    {
        $output = $this->launchJob(['-vvv' => true]);
        $outputContent = $output->fetch();
        $this->assertStringContainsString('DEBUG', $outputContent);
        $this->assertStringContainsString('Export csv_product_export has been successfully executed.', $outputContent);
    }

    public function testLaunchJobWithValidEmail()
    {
        $output = $this->launchJob(['--email' => 'ziggy@akeneo.com']);
        $this->assertEquals('Export csv_product_export has been successfully executed.' . PHP_EOL, $output->fetch());
    }

    public function testLaunchJobWithInvalidJobInstance()
    {
        $output = $this->launchJob(['code' => 'unknown_command']);
        $this->assertStringContainsString('Could not find job instance "unknown_command".', $output->fetch());
    }

    public function testLaunchJobWithInvalidEmail()
    {
        $output = $this->launchJob(['--email' => 'email']);
        $this->assertStringContainsString('Email "email" is invalid', $output->fetch());
    }

    public function testLaunchJobWithInvalidJobExecutionCode()
    {
        $output = $this->launchJob(['execution' => '1']);
        $this->assertStringContainsString('Could not find job execution "1"', $output->fetch());
    }

    public function testLaunchJobAlreadyStarted()
    {
        $this->launchJob();
        $jobExecution = $this->getJobExecution();

        $this->assertEquals(BatchStatus::COMPLETED, $jobExecution['status']);

        $output = $this->launchJob(['execution' => $jobExecution['id']]);
        $this->assertStringContainsString(sprintf('Job execution "%s" has invalid status: COMPLETED', $jobExecution['id']), $output->fetch());
    }

    public function testLaunchJobExecutionWithConfigOverridden()
    {
        $output = $this->launchJob(['execution' => '1', '--config' => ['filePath' => '/tmp/foo']]);
        $this->assertStringContainsString('Configuration option cannot be specified when launching a job execution.', $output->fetch());
    }

    public function testLaunchJobExecutionWithUsernameOverridden()
    {
        $output = $this->launchJob(['execution' => '1', '--username' => 'mary']);
        $this->assertStringContainsString('Username option cannot be specified when launching a job execution', $output->fetch());
    }

    /**
     * @param array $arrayInput
     *
     * @return BufferedOutput
     */
    protected function launchJob(array $arrayInput = [])
    {
        $this->resetShellVerbosity();
        $application = new Application(static::$kernel);
        $application->setAutoExit(false);

        $defaultArrayInput = [
            'command'  => 'akeneo:batch:job',
            'code'     => 'csv_product_export',
        ];

        $arrayInput = array_merge($defaultArrayInput, $arrayInput);
        if (isset($arrayInput['--config'])) {
            $arrayInput['--config'] = json_encode($arrayInput['--config']);
        }

        $input = new ArrayInput($arrayInput);
        $output = new BufferedOutput();
        $application->run($input, $output);

        return $output;
    }

    /**
     * @return Connection
     */
    protected function getConnection(): Connection
    {
        return $this->get('doctrine.orm.entity_manager')->getConnection();
    }

    /**
     * @return array
     */
    protected function getJobExecution(): array
    {
        $connection = $this->getConnection();
        $stmt = $connection->prepare('SELECT * from akeneo_batch_job_execution');
        $stmt->execute();
        return $stmt->fetch();
    }

    /**
     * @param string $identifier
     * @param array  $data
     *
     * @return ProductInterface
     */
    protected function createProduct(string $identifier, array $data = []): ProductInterface
    {
        $product = $this->get('pim_catalog.builder.product')->createProduct($identifier);
        $this->get('pim_catalog.updater.product')->update($product, $data);
        $this->get('pim_catalog.saver.product')->save($product);

        $this->get('akeneo_elasticsearch.client.product_and_product_model')->refreshIndex();

        return $product;
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration()
    {
        return $this->catalog->useTechnicalCatalog();
    }

    /**
     * When running an application command, an environment variable is set with the verbosity level.
     * This environment variable is not reset when running another application command.
     *
     * With process isolation of phpunit deactivated, a test running an application command
     * impacts the next test, which will be executed in verbose mode also due to this stateful environment variable.
     *
     * This function resets the state.
     */
    private function resetShellVerbosity()
    {
        putenv('SHELL_VERBOSITY=0');
        $_ENV['SHELL_VERBOSITY'] = 0;
        $_SERVER['SHELL_VERBOSITY'] = 0;
    }
}
