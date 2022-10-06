<?php

namespace Akeneo\Platform\TailoredImport\Application\CreateTailoredImport;

use Akeneo\Platform\Job\ServiceApi\JobInstance\CreateJobInstance\CreateJobInstanceCommand;
use Akeneo\Platform\Job\ServiceApi\JobInstance\CreateJobInstance\CreateJobInstanceHandlerInterface;
use Akeneo\Platform\TailoredImport\Domain\Query\Filesystem\StoreFileInterface;
use Akeneo\Platform\TailoredImport\ServiceApi\CreateTailoredImportCommand;
use Akeneo\Platform\TailoredImport\ServiceApi\CreateTailoredImportHandlerInterface;
use Akeneo\Platform\TailoredImport\ServiceApi\CreateTailoredImportResult;
use Akeneo\Platform\TailoredImport\ServiceApi\File;
use Akeneo\Tool\Bundle\BatchBundle\Job\JobInstanceRepository;

class CreateTailoredImportHandler implements CreateTailoredImportHandlerInterface
{
    private const TYPE = 'import';
    private const CONNECTOR = 'Akeneo Tailored Import';
    private const JOB_NAME = 'xlsx_tailored_product_import';
    private const JOB_INSTANCE_EDIT_URL = '/collect/import/%s/edit';

    public function __construct(
        private CreateJobInstanceHandlerInterface $createJobInstanceHandler,
        private StoreFileInterface $fileStorer,
        private JobInstanceRepository $jobInstanceRepository,
    ) {
    }

    public function handle(CreateTailoredImportCommand $createTailoredImportCommand): CreateTailoredImportResult
    {
        $code = $createTailoredImportCommand->code;
        $fileTemplate = $createTailoredImportCommand->fileTemplate;
        $label = $createTailoredImportCommand->label;

        if (null !== $this->jobInstanceRepository->findOneByIdentifier($code)) {
            throw new \RuntimeException('Job '.$code.' already exists.');
        }

        $rawParameters = $this->buildRawParameters($fileTemplate);

        $createJobInstanceCommand = new CreateJobInstanceCommand(
            type: self::TYPE,
            code: $code,
            label: $label,
            connector: self::CONNECTOR,
            jobName: self::JOB_NAME,
            rawParameters: $rawParameters,
        );

        $this->createJobInstanceHandler->handle($createJobInstanceCommand);

        $jobInstanceEditUrl = sprintf(self::JOB_INSTANCE_EDIT_URL, $code);

        return new CreateTailoredImportResult($jobInstanceEditUrl);
    }

    private function buildRawParameters(File $fileTemplate): array
    {
        $fileMetadata = stream_get_meta_data($fileTemplate->getResource());
        $fileName = substr($fileMetadata['uri'], strrpos($fileMetadata['uri'], '/') + 1);

        $fileInfo = $this->fileStorer->store($fileMetadata['uri'], $fileName);

        $fileKey = $fileInfo->normalize()['filePath'];

        return [
            'file_key' => $fileKey,
        ];
    }
}
