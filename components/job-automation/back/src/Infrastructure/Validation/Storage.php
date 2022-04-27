<?php

namespace Akeneo\Platform\JobAutomation\Infrastructure\Validation;

use Symfony\Component\Validator\Constraint;

class Storage extends Constraint
{
    public const UNAVAILABLE_TYPE = 'akeneo.job_automation.validation.storage.unavailable_type';

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
