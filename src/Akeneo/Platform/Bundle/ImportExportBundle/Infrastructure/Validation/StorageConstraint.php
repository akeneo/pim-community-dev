<?php

namespace Akeneo\Platform\Bundle\ImportExportBundle\Infrastructure\Validation;

use Symfony\Component\Validator\Constraint;

abstract class StorageConstraint extends Constraint
{
    public function __construct(
        /** @var string[] */
        private array $filePathSupportedFileExtensions = [],
    ) {
        parent::__construct();
    }

    /** @param string[] $filePathSupportedFileExtensions */
    public function setFilePathSupportedFileExtensions(array $filePathSupportedFileExtensions): void
    {
        $this->filePathSupportedFileExtensions = $filePathSupportedFileExtensions;
    }

    public function getFilePathSupportedFileExtensions(): array
    {
        return $this->filePathSupportedFileExtensions;
    }
}
