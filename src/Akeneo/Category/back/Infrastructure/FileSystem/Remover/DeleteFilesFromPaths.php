<?php

namespace Akeneo\Category\Infrastructure\FileSystem\Remover;

interface DeleteFilesFromPaths
{
    /**
     * @param array<string> $filePaths
     */
    public function __invoke(array $filePaths): void;
}
