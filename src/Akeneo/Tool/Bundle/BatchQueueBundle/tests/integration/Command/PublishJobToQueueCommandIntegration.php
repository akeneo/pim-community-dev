<?php

declare(strict_types=1);

namespace Akeneo\Bundle\BatchQueueBundle\tests\integration\Command;;

use Akeneo\Bundle\BatchQueueBundle\Command\PublishJobToQueueCommand;
use Akeneo\Component\Batch\Job\BatchStatus;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\IntegrationTestsBundle\Launcher\JobLauncher;
use Akeneo\Test\Integration\TestCase;
use Doctrine\DBAL\Driver\Connection;
use Pim\Component\Catalog\Model\ProductInterface;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;

class PublishJobToQueueCommandIntegration extends TestCase
{
    const EXPORT_DIRECTORY = 'pim-integration-tests-export';

    /** @var JobLauncher */
    protected $jobLauncher;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();

        $this->jobLauncher = new JobLauncher(static::$kernel);

        $this->createProduct('product_1');
        $this->createProduct('product_2');
    }

    public function testPushJobExecutionIntoQueue()
    {
        $output = $this->pushJob();
        $jobExecution = $this->getJobExecution();

        $this->assertEquals(BatchStatus::STARTING, $jobExecution['status']);
        $this->assertNull($jobExecution['start_time']);
        $this->assertNull($jobExecution['end_time']);
        $this->assertNotNull($jobExecution['create_time']);
        $this->assertNull($jobExecution['pid']);
        $this->assertNull($jobExecution['log_file']);
        $this->assertNotNull(json_decode($jobExecution['raw_parameters'], true));
        $this->assertNull($jobExecution['user']);

        $jobExecutionMessage  =$this->getJobExecutionMessage();

        $this->assertEquals(1, $jobExecutionMessage['id']);
        $this->assertNotNull($jobExecutionMessage['job_execution_id']);
        $this->assertEquals('{"env":"test"}', $jobExecutionMessage['options']);
        $this->assertNotNull($jobExecutionMessage['create_time']);
        $this->assertNull($jobExecutionMessage['updated_time']);
        $this->assertNull($jobExecutionMessage['consumer']);

        $this->assertEquals('Export csv_product_export has been successfully pushed into the queue.' . PHP_EOL, $output->fetch());

        $this->jobLauncher->launchConsumerOnce();

        $jobExecution = $this->getJobExecution();
        $this->assertEquals(BatchStatus::COMPLETED, $jobExecution['status']);
    }

    public function testPushJobExecutionIntoQueueWithUsername()
    {
        $this->pushJob(['--username' => 'mary']);
        $jobExecution = $this->getJobExecution();

        $this->assertEquals('mary', $jobExecution['user']);
        $jobExecutionMessage  =$this->getJobExecutionMessage();

        $this->assertEquals(1, $jobExecutionMessage['id']);
    }

    public function testPushJobExecutionWithConfigOverridden()
    {
        $filePath= sys_get_temp_dir() . DIRECTORY_SEPARATOR . self::EXPORT_DIRECTORY . DIRECTORY_SEPARATOR . 'new_export.csv';
        if (file_exists($filePath)) {
            unlink($filePath);
        }

        $this->pushJob(['--config' => ['filePath' => $filePath]]);

        $jobExecution = $this->getJobExecution();

        $config = json_decode($jobExecution['raw_parameters'], true);
        $this->assertEquals($filePath, $config['filePath']);

        $this->jobLauncher->launchConsumerOnce();

        $this->assertTrue(file_exists($filePath));
    }

    public function testPushJobExecutionWithNoLog()
    {
        $this->pushJob(['--no-log' => true]);
        $jobExecutionMessage = $this->getJobExecutionMessage();

        $this->assertEquals('{"env":"test","no-log":true}', $jobExecutionMessage['options']);
    }

    public function testLaunchJobWithValidEmail()
    {
        $this->pushJob(['--email' => 'ziggy@akeneo.com']);
        $jobExecutionMessage = $this->getJobExecutionMessage();

        $this->assertEquals('{"env":"test","email":"ziggy@akeneo.com"}', $jobExecutionMessage['options']);
    }

    public function testLaunchJobWithInvalidJobInstance()
    {
        $output = $this->pushJob(['code' => 'unknown_command']);
        $this->assertContains('Could not find job instance "unknown_command".', $output->fetch());
    }

    public function testLaunchJobWithInvalidEmail()
    {
        $output = $this->pushJob(['--email' => 'email']);
        $this->assertContains('Email "email" is invalid', $output->fetch());
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
     * @return array
     */
    protected function getJobExecutionMessage(): array
    {
        $connection = $this->getConnection();
        $stmt = $connection->prepare('SELECT * from akeneo_batch_job_execution_queue');
        $stmt->execute();
        return $stmt->fetch();
    }

    /**
     * @param array $arrayInput
     *
     * @return BufferedOutput
     */
    protected function pushJob(array $arrayInput = [])
    {
        $application = new Application(static::$kernel);
        $application->setAutoExit(false);

        $defaultArrayInput = [
            'command'  => PublishJobToQueueCommand::COMMAND_NAME,
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
        return $this->catalog->useTechnicalCatalog();
    }
}
