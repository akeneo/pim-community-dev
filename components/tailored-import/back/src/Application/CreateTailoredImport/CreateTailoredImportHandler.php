<?php

namespace Akeneo\Platform\TailoredImport\Application\CreateTailoredImport;

use Akeneo\Platform\Job\ServiceApi\JobInstance\CreateJobInstance\CreateJobInstanceCommand;
use Akeneo\Platform\Job\ServiceApi\JobInstance\CreateJobInstance\CreateJobInstanceHandlerInterface;
use Akeneo\Platform\TailoredImport\Domain\Query\Filesystem\StoreFileInterface;
use Akeneo\Platform\TailoredImport\ServiceApi\CreateTailoredImportCommand;
use Akeneo\Platform\TailoredImport\ServiceApi\CreateTailoredImportHandlerInterface;
use Akeneo\Platform\TailoredImport\ServiceApi\CreateTailoredImportResult;
use Akeneo\Platform\TailoredImport\ServiceApi\File;
use Symfony\Component\Routing\RouterInterface;

class CreateTailoredImportHandler implements CreateTailoredImportHandlerInterface
{
    private const TYPE = 'import';
    private const CONNECTOR = 'Akeneo Tailored Import';
    private const JOB_NAME = 'xlsx_tailored_product_import';

    public function __construct(
        private CreateJobInstanceHandlerInterface $createJobInstanceHandler,
        private StoreFileInterface $fileStorer,
        private RouterInterface $router,
    ) {
    }

    public function handle(CreateTailoredImportCommand $createTailoredImportCommand): CreateTailoredImportResult
    {
        $code = $createTailoredImportCommand->code;
        $fileTemplate = $createTailoredImportCommand->fileTemplate;
        $label = $createTailoredImportCommand->label;

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

        $jobInstanceEditUrl = $this->router->generate('pim_importexport_import_profile_edit', [
            'code' => $code,
        ]);

        return new CreateTailoredImportResult($jobInstanceEditUrl);
    }

    private function buildRawParameters(File $fileTemplate): array
    {
        $fileMetadata = stream_get_meta_data($fileTemplate->getResource());
        $fileName = $fileTemplate->getFileName();

        $fileInfo = $this->fileStorer->store($fileMetadata['uri'], $fileName);

        $fileKey = $fileInfo->normalize()['filePath'];

        return [
            'file_key' => $fileKey,
        ];
    }
}
