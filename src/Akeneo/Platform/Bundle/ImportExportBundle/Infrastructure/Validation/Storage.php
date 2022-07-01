<?php

namespace Akeneo\Platform\Bundle\ImportExportBundle\Infrastructure\Validation;

use Symfony\Component\Validator\Constraint;

class Storage extends Constraint
{
    public const UNAVAILABLE_TYPE = 'pim_import_export.form.job_instance.validation.storage.unavailable_type';

    public function __construct(
        /** @var string[] */
        private array $filePathSupportedFileExtensions,
    ) {
        parent::__construct();
    }

    public function getFilePathSupportedFileExtensions(): array
    {
        return $this->filePathSupportedFileExtensions;
    }
}
