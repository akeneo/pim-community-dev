<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\AssetManager\Infrastructure\Transformation\Operation;

use Symfony\Component\HttpFoundation\File\File;

class TemporaryFileFactory
{
    /**
     * Creates a file into the tmp directory from a file content
     *
     * @param string $content
     * @param string|null $prefix

     * @return File
     */
    public function createFromContent(string $content, ?string $prefix = null): File
    {
        $tmpFile = tempnam(sys_get_temp_dir(), $prefix ?? 'akeneo_asset_manager_');
        file_put_contents($tmpFile, $content);

        return new File($tmpFile, false);
    }
}
