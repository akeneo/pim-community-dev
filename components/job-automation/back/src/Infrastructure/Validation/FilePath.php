<?php

namespace Akeneo\Platform\JobAutomation\Infrastructure\Validation;

use Symfony\Component\Validator\Constraint;

class FilePath extends Constraint
{
    public const UNSUPPORTED_EXTENSION = 'akeneo.job_automation.validation.file_path.unsupported_extension';

    public function __construct(
        /** @var string[] */
        private array $supportedFileExtensions,
    ) {
        parent::__construct();
    }

    public function getSupportedFileExtensions(): array
    {
        return $this->supportedFileExtensions;
    }
}
