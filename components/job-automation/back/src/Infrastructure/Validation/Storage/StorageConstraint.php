<?php

namespace Akeneo\Platform\JobAutomation\Infrastructure\Validation\Storage;

use Symfony\Component\Validator\Constraint;

abstract class StorageConstraint extends Constraint
{
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
