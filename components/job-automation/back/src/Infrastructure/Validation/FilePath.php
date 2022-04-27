<?php

namespace Akeneo\Platform\JobAutomation\Infrastructure\Validation;

use Symfony\Component\Validator\Constraint;

class FilePath extends Constraint
{
    public const BAD_EXTENSION = 'akeneo.job_automation.validation.file_path.bad_extension';

    public function __construct(
        /** @var string[] */
        private array $allowedFileExtensions,
    ) {
        parent::__construct();
    }

    public function getAllowedFileExtensions(): array
    {
        return $this->allowedFileExtensions;
    }
}
