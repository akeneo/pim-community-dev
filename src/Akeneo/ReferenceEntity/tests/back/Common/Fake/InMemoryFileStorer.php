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

namespace Akeneo\ReferenceEntity\Common\Fake;

use Akeneo\Tool\Component\FileStorage\File\FileStorerInterface;
use Akeneo\Tool\Component\FileStorage\Model\FileInfo;

/**
 * @author    Laurent Petard <laurent.petard@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class InMemoryFileStorer implements FileStorerInterface
{
    public const FILES_PATH = 'in/memory/files/';

    /**
     * {@inheritdoc}
     */
    public function store(\SplFileInfo $rawFile, $destFsAlias, $deleteRawFile = false)
    {
        $file = new FileInfo();
        $file->setKey(self::FILES_PATH . $rawFile->getFilename());
        $file->setOriginalFilename($rawFile->getFilename());

        return $file;
    }
}
