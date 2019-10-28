<?php

declare(strict_types=1);

namespace Akeneo\Tool\Bundle\BatchQueueBundle\tests\integration\Queue;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Test\Integration\TestCase;
use Akeneo\Test\IntegrationTestsBundle\Launcher\JobLauncher;
use Akeneo\Tool\Component\Batch\Job\BatchStatus;
use Akeneo\Tool\Component\BatchQueue\Queue\PublishJobToQueue;
use Doctrine\DBAL\Driver\Connection;
use InvalidArgumentException;
use RuntimeException;

class PublishToJobQueueIntegration extends TestCase
{
    const EXPORT_DIRECTORY = 'pim-integration-tests-export';

    /** @var JobLauncher */
    protected $jobLauncher;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->jobLauncher = $this->get('akeneo_integration_tests.launcher.job_launcher');

        $this->createProduct('product_1');
        $this->createProduct('product_2');
    }

    public function testPushJobExecutionIntoQueue()
    {
        /** @var PublishJobToQueue $publishJobToQueue */
        $publishJobToQueue = $this->get('akeneo_batch_queue.queue.publish_job_to_queue');
        $publishJobToQueue->publish(
            'csv_product_export',
            []
        );

        $jobExecution = $this->getJobExecution();

        $this->assertEquals(BatchStatus::STARTING, $jobExecution['status']);
        $this->assertNull($jobExecution['start_time']);
        $this->assertNull($jobExecution['end_time']);
        $this->assertNotNull($jobExecution['create_time']);
        $this->assertNull($jobExecution['pid']);
        $this->assertNull($jobExecution['log_file']);
        $this->assertNotNull(json_decode($jobExecution['raw_parameters'], true));
        $this->assertNull($jobExecution['user']);

        $jobExecutionMessage = $this->getJobExecutionMessage();

        $this->assertNotNull($jobExecutionMessage['job_execution_id']);
        $this->assertJsonStringEqualsJsonString('{"env": "test"}', $jobExecutionMessage['options']);
        $this->assertNotNull($jobExecutionMessage['create_time']);
        $this->assertNull($jobExecutionMessage['updated_time']);
        $this->assertNull($jobExecutionMessage['consumer']);

        $this->jobLauncher->launchConsumerOnce();

        $jobExecution = $this->getJobExecution();
        $this->assertEquals(BatchStatus::COMPLETED, $jobExecution['status']);
    }

    public function testPushJobExecutionIntoQueueWithUsername()
    {
        /** @var PublishJobToQueue $publishJobToQueue */
        $publishJobToQueue = $this->get('akeneo_batch_queue.queue.publish_job_to_queue');
        $publishJobToQueue->publish(
            'csv_product_export',
            [],
            false,
            'mary'
        );

        $jobExecution = $this->getJobExecution();

        $this->assertEquals('mary', $jobExecution['user']);
        $jobExecutionMessage = $this->getJobExecutionMessage();

        $this->assertNotNull($jobExecutionMessage['id']);
    }

    public function testPushJobExecutionWithConfigOverridden()
    {
        $filePath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . self::EXPORT_DIRECTORY . DIRECTORY_SEPARATOR . 'new_export.csv';
        if (file_exists($filePath)) {
            unlink($filePath);
        }

        /** @var PublishJobToQueue $publishJobToQueue */
        $publishJobToQueue = $this->get('akeneo_batch_queue.queue.publish_job_to_queue');
        $publishJobToQueue->publish(
            'csv_product_export',
            ['filePath' => $filePath]
        );

        $jobExecution = $this->getJobExecution();

        $config = json_decode($jobExecution['raw_parameters'], true);
        $this->assertEquals($filePath, $config['filePath']);

        $this->jobLauncher->launchConsumerOnce();

        $this->assertTrue(file_exists($filePath));
    }

    public function testPushJobExecutionWithNoLog()
    {
        /** @var PublishJobToQueue $publishJobToQueue */
        $publishJobToQueue = $this->get('akeneo_batch_queue.queue.publish_job_to_queue');
        $publishJobToQueue->publish(
            'csv_product_export',
            [],
            true
        );

        $jobExecutionMessage = $this->getJobExecutionMessage();

        $this->assertJsonStringEqualsJsonString(
            '{"env": "test", "no-log": true}',
            $jobExecutionMessage['options']
        );
    }

    public function testLaunchJobWithValidEmail()
    {
        /** @var PublishJobToQueue $publishJobToQueue */
        $publishJobToQueue = $this->get('akeneo_batch_queue.queue.publish_job_to_queue');
        $publishJobToQueue->publish(
            'csv_product_export',
            [],
            false,
            null,
            'ziggy@akeneo.com'
        );

        $jobExecutionMessage = $this->getJobExecutionMessage();

        $this->assertJsonStringEqualsJsonString(
            '{"env": "test", "email": "ziggy@akeneo.com"}',
            $jobExecutionMessage['options']
        );
    }

    public function testLaunchJobWithInvalidJobInstance()
    {
        $this->expectException(InvalidArgumentException::class);

        /** @var PublishJobToQueue $publishJobToQueue */
        $publishJobToQueue = $this->get('akeneo_batch_queue.queue.publish_job_to_queue');
        $publishJobToQueue->publish(
            'unknown_command',
            []
        );
    }

    public function testLaunchJobWithInvalidEmail()
    {
        $this->expectException(RuntimeException::class);

        /** @var PublishJobToQueue $publishJobToQueue */
        $publishJobToQueue = $this->get('akeneo_batch_queue.queue.publish_job_to_queue');
        $publishJobToQueue->publish(
            'csv_product_export',
            [],
            false,
            null,
            'email'
        );
    }

    private function getJobExecution(): array
    {
        $connection = $this->getConnection();
        $stmt = $connection->prepare('SELECT * from akeneo_batch_job_execution');
        $stmt->execute();

        return $stmt->fetch();
    }

    private function createProduct(string $identifier, array $data = []): ProductInterface
    {
        $product = $this->get('pim_catalog.builder.product')->createProduct($identifier);
        $this->get('pim_catalog.updater.product')->update($product, $data);
        $this->get('pim_catalog.saver.product')->save($product);

        $this->get('akeneo_elasticsearch.client.product_and_product_model')->refreshIndex();

        return $product;
    }

    protected function getConfiguration()
    {
        return $this->catalog->useTechnicalCatalog();
    }

    protected function getConnection(): Connection
    {
        return $this->get('doctrine.orm.entity_manager')->getConnection();
    }

    protected function getJobExecutionMessage(): array
    {
        $connection = $this->getConnection();
        $stmt = $connection->prepare('SELECT * from akeneo_batch_job_execution_queue');
        $stmt->execute();

        return $stmt->fetch();
    }
}
