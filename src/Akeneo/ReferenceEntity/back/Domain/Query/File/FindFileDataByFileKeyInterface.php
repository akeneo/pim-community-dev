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

namespace Akeneo\ReferenceEntity\Domain\Query\File;

/**
 * Find the data of a file by its file key.
 *
 * Example:
 * [
 *    'filePath'         => '0/c/b/0/0cb0c0e115dedba676f8d1ad8343ec207ab54c7b_kartell.jpg',
 *    'originalFilename' => 'kartell.jpg',
 *    'size'             => 1024,
 *    'mimeType'         => 'image/jpg',
 *    'extension'        => 'jpg'
 * ]
 */
interface FindFileDataByFileKeyInterface
{
    public function __invoke(string $fileKey): ?array;
}
