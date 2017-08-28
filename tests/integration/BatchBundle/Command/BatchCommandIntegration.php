<?php

declare(strict_types=1);

namespace Akeneo\Test\Integration\integration\BatchBundle\Command;

use Akeneo\Component\Batch\Job\BatchStatus;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Pim\Component\Catalog\Model\ProductInterface;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;

class BatchCommandIntegration extends TestCase
{
    const EXPORT_DIRECTORY = 'pim-integration-tests-export';

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();

        $this->createProduct('product_1');
        $this->createProduct('product_2');
    }

    public function testJobExecutionStateWhenJobIsCompleted()
    {
        $output = $this->launchJob();
        $connection = $this->get('doctrine.orm.default_entity_manager')->getConnection();
        $stmt = $connection->prepare('SELECT status, pid, start_time, end_time, create_time, user, log_file, raw_parameters from akeneo_batch_job_execution');
        $stmt->execute();
        $result = $stmt->fetch();

        $this->assertEquals(BatchStatus::COMPLETED, $result['status']);
        $this->assertNotNull($result['start_time']);
        $this->assertNotNull($result['end_time']);
        $this->assertNotNull($result['create_time']);
        $this->assertNotNull($result['pid']);
        $this->assertNotNull($result['log_file']);
        $this->assertNotNull(json_decode($result['raw_parameters'], true));
        $this->assertNull($result['user']);
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
        $this->assertContains('DEBUG', $outputContent);
        $this->assertContains('Export csv_product_export has been successfully executed.', $outputContent);
    }

    public function testLaunchJobWithValidEmail()
    {
        $output = $this->launchJob(['--email' => 'ziggy@akeneo.com']);
        $this->assertEquals('Export csv_product_export has been successfully executed.' . PHP_EOL, $output->fetch());
    }

    public function testLaunchJobWithInvalidJobInstance()
    {
        $output = $this->launchJob(['code' => 'unknown_command']);
        $this->assertContains('Could not find job instance "unknown_command".', $output->fetch());
    }

    public function testLaunchJobWithInvalidEmail()
    {
        $output = $this->launchJob(['--email' => 'email']);
        $this->assertContains('Email "email" is invalid', $output->fetch());
    }

    public function testLaunchJobWithInvalidJobExecutionCode()
    {
        $output = $this->launchJob(['execution' => '1']);
        $this->assertContains('Could not find job execution "1"', $output->fetch());
    }

    public function testLaunchJobAlreadyStarted()
    {
        $this->launchJob();
        $connection = $this->get('doctrine.orm.default_entity_manager')->getConnection();
        $stmt = $connection->prepare('SELECT id, status from akeneo_batch_job_execution');
        $stmt->execute();
        $result = $stmt->fetch();

        $this->assertEquals(BatchStatus::COMPLETED, $result['status']);

        $output = $this->launchJob(['execution' => $result['id']]);
        $this->assertContains('Job execution "20" has invalid status: COMPLETED', $output->fetch());
    }

    public function testLaunchJobExecutionWithConfigOverridden()
    {
        $output = $this->launchJob(['execution' => '1', '--config' => ['filePath' => '/tmp/foo']]);
        $this->assertContains('Configuration option cannot be specified when launching a job execution.', $output->fetch());
    }

    /**
     * @param array $arrayInput
     *
     * @throws \Exception
     *
     * @return BufferedOutput
     */
    protected function launchJob(array $arrayInput = [])
    {
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

        $this->get('akeneo_elasticsearch.client.product')->refreshIndex();

        return $product;
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration()
    {
        return new Configuration(
            [Configuration::getTechnicalCatalogPath()]
        );
    }
}
