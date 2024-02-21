<?php

declare(strict_types=1);

namespace Akeneo\Test\Acceptance\FileStorage;

use Akeneo\Tool\Component\FileStorage\File\FileStorerInterface;
use Akeneo\Tool\Component\FileStorage\Model\FileInfo;
use Akeneo\Tool\Component\FileStorage\Model\FileInfoInterface;

/**
 * @author    Christophe Chausseray <christophe.chausseray@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class InMemoryFileStorer implements FileStorerInterface
{
    /**
     * {@inheritdoc}
     */
    public function store(\SplFileInfo $rawFile, string $destFsAlias, bool $deleteRawFile = false): FileInfoInterface
    {
        $file = new FileInfo();
        $file->setKey($rawFile->getPathname());
        $file->setOriginalFilename($rawFile->getFilename());

        return $file;
    }
}
