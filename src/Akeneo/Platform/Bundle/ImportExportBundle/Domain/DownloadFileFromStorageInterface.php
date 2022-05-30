<?php

namespace Akeneo\Platform\Bundle\ImportExportBundle\Domain;

use Akeneo\Platform\Bundle\ImportExportBundle\Domain\Model\StorageInterface;

interface DownloadFileFromStorageInterface
{
    public function download(StorageInterface $sourceStorage, string $workingDirectory): string;
}
