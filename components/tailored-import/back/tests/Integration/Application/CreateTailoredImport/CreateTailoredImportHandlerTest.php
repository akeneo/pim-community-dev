<?php

namespace Akeneo\Platform\TailoredImport\Test\Integration\Application\CreateTailoredImport;

use Akeneo\Platform\TailoredImport\Application\CreateTailoredImport\CreateTailoredImportHandler;
use Akeneo\Platform\TailoredImport\ServiceApi\CreateTailoredImportResult;
use Akeneo\Tool\Bundle\BatchBundle\Job\JobInstanceRepository;
use Akeneo\Tool\Component\Batch\Model\JobInstance;
use Akeneo\Platform\TailoredImport\ServiceApi\CreateTailoredImportCommand;
use Akeneo\Platform\TailoredImport\Test\Integration\IntegrationTestCase;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Platform\TailoredImport\ServiceApi\File;

final class CreateTailoredImportHandlerTest extends IntegrationTestCase
{
    private CreateTailoredImportHandler $handler;
    private JobInstanceRepository $jobInstanceRepository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->handler = $this->get('akeneo.tailored_import.handler.create_tailored_import_handler');
        $this->jobInstanceRepository = $this->get('akeneo_batch.job.job_instance_repository');
    }

    /**
     * @test
     */
    public function it_creates_a_new_tailored_import_job_instance(): void
    {
        $fileName = 'simple_import.xlsx';
        $file = fopen(__DIR__ . '/../../../Common/simple_import.xlsx', 'r');

        $command = new CreateTailoredImportCommand(
            'test_tailored_import',
            new File($fileName, $file),
            'Test Tailored Import'
        );

        $jobInstanceEditUrl = $this->handler->handle($command);
        fclose($file);

        $actual = $this->jobInstanceRepository->findOneByIdentifier('test_tailored_import');

        $this->assertInstanceOf(JobInstance::class, $actual);

        $expectedEditUrl = new CreateTailoredImportResult('/collect/import/test_tailored_import/edit');
        $this->assertEquals($expectedEditUrl, $jobInstanceEditUrl);
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useTechnicalCatalog();
    }
}
