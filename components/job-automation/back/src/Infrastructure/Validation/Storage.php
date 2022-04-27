<?php

namespace Akeneo\Platform\JobAutomation\Infrastructure\Validation;

use Symfony\Component\Validator\Constraint;

class Storage extends Constraint
{
    public function __construct(
        /** @var string[] */
        private array $filePathAllowedFileExtensions,
    ) {
        parent::__construct();
    }

    public function getFilePathAllowedFileExtensions(): array
    {
        return $this->filePathAllowedFileExtensions;
    }
}
