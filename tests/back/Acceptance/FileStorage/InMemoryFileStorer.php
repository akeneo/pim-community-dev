<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

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
    public function store(\SplFileInfo $rawFile, $destFsAlias, $deleteRawFile = false): FileInfoInterface
    {
        $file = new FileInfo();
        $file->setKey($rawFile->getPathname());
        $file->setOriginalFilename($rawFile->getFilename());

        return $file;
    }
}
