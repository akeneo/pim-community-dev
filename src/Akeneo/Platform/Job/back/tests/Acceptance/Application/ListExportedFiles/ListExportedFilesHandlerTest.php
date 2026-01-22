<?php

declare(strict_types=1);

namespace Akeneo\Platform\Job\Test\Acceptance\Application\ListExportedFiles;

use Akeneo\Tool\Bundle\BatchBundle\JobExecution\ExecuteJobExecutionHandlerInterface;
use Akeneo\Pim\Enrichment\Product\API\Command\UpsertProductCommand;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetCategories;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetFileValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetImageValue;
use Akeneo\Pim\Enrichment\Product\API\ValueObject\ProductUuid;
use Akeneo\Platform\Job\Application\ListExportedFiles\ListExportedFilesHandler;
use Akeneo\Platform\Job\ServiceApi\JobExecution\ListExportedFiles\ListExportedFilesQuery;
use Akeneo\Test\Integration\TestCase;
use Akeneo\Tool\Bundle\BatchBundle\JobExecution\CreateJobExecutionHandlerInterface;
use Ramsey\Uuid\Uuid;

class ListExportedFilesHandlerTest extends TestCase
{
    public const JOB_INSTANCE_CODE = 'csv_product_export';

    private $handler;
    private $jobInstanceRepository;
    private $jobRepository;
    private $createJobExecutionHandler;
    private $executeJobExecutionHandler;
    private int $adminId;

    protected function setUp(): void
    {
        parent::setUp();
        $this->handler = $this->get(ListExportedFilesHandler::class);
        $this->jobInstanceRepository = $this->get('pim_enrich.repository.job_instance');
        $this->jobRepository = $this->get('akeneo_batch.job_repository');
        $this->createJobExecutionHandler = $this->get(CreateJobExecutionHandlerInterface::class);
        $this->executeJobExecutionHandler = $this->get(ExecuteJobExecutionHandlerInterface::class);

        $this->get('akeneo_integration_tests.helper.authenticator')->logIn('admin');
        $this->adminId = $this->getUserId('admin');

        $this->createProduct('1111a11a-65b7-418f-b713-ac25b0291131', [
            new SetCategories(['master']),
            new SetImageValue('an_image', null, null, $this->getFileInfoKey($this->getFixturePath('akeneo.jpg'))),
        ]);
        $this->createProduct('2222a22a-65b7-418f-b713-ac25b0291131', [
            new SetCategories(['master']),
            new SetFileValue('a_file', null, null, $this->getFileInfoKey($this->getFixturePath('akeneo.pdf'))),
        ]);
        $this->createProduct('3333a33a-65b7-418f-b713-ac25b0291131', [new SetCategories(['master'])]);
        $this->createProduct('4444a44a-65b7-418f-b713-ac25b0291131', [new SetCategories(['master'])]);
        $this->createProduct('5555a55a-65b7-418f-b713-ac25b0291131', [new SetCategories(['master'])]);
    }

    /**
     * @test
     */
    public function test_it_lists_export_filenames_without_media()
    {
        $jobInstance = $this->jobInstanceRepository->findOneBy(['code' => self::JOB_INSTANCE_CODE]);
        $jobExecution = $this->createJobExecutionHandler->createFromJobInstance($jobInstance, [], null);

        $this->jobRepository->updateJobExecution($jobExecution);
        $jobExecution = $this->executeJobExecutionHandler->executeFromJobExecutionId($jobExecution->getId());

        $listQuery = new ListExportedFilesQuery($jobExecution->getId(), false);
        $result = $this->handler->handle($listQuery);

        foreach ($result as $filepath) {
            $this->assertMatchesRegularExpression('/export\/'.self::JOB_INSTANCE_CODE.'\/[0-9]+\/output\/products\.csv/', $filepath);
        }
    }

    /**
     * @test
     */
    public function test_it_lists_export_filenames_with_media()
    {
        $jobInstance = $this->jobInstanceRepository->findOneBy(['code' => self::JOB_INSTANCE_CODE]);
        $jobExecution = $this->createJobExecutionHandler->createFromJobInstance($jobInstance, ['with_media' => true], null);

        $this->jobRepository->updateJobExecution($jobExecution);

        $jobExecution = $this->executeJobExecutionHandler->executeFromJobExecutionId($jobExecution->getId());

        $listQuery = new ListExportedFilesQuery($jobExecution->getId(), true);
        $result = $this->handler->handle($listQuery);

        $expectedFilepaths = [
            '/export\/'.self::JOB_INSTANCE_CODE.'\/[0-9]+\/output\/files\/([0-9a-z]+(-)*){5}\/an_image\/akeneo\.jpg/',
            '/export\/'.self::JOB_INSTANCE_CODE.'\/[0-9]+\/output\/files\/([0-9a-z]+(-)*){5}\/a_file\/akeneo\.pdf/',
            '/export\/'.self::JOB_INSTANCE_CODE.'\/[0-9]+\/output\/products\.csv/',
        ];

        foreach ($result as $index => $filepath) {
            $this->assertMatchesRegularExpression($expectedFilepaths[$index], $filepath);
        }
    }

    private function createProduct(string $uuid, array $userIntents): void
    {
        $this->get('pim_enrich.product.message_bus')->dispatch(
            UpsertProductCommand::createWithUuid(
                $this->adminId,
                ProductUuid::fromUuid(Uuid::fromString($uuid)),
                $userIntents
            )
        );
        $this->get('akeneo_elasticsearch.client.product_and_product_model')->refreshIndex();
    }

    protected function getConfiguration()
    {
        return $this->catalog->useTechnicalCatalog();
    }
}
