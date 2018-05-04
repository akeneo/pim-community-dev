<?php

namespace Akeneo\Tool\Component\FileStorage;

use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Generates all the path data (sanitized and unique filename, path, pathname and uuid) of a file.
 *
 * @author    Julien Janvier <jjanvier@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class PathGenerator implements PathGeneratorInterface
{
    /**
     * Generate all the path data of a file. If the original file name exceeds 100 characters, it is truncated.
     * The file name is sanitized, and a unique ID is prepended.
     *
     * For example, a file called "this i#s the é file.txt'" will produce the following output:
     *   [
     *      'uuid'      => '2fd4e1c67a2d28fced849ee1bb76e7391b93eb12',
     *      'file_name' => '2fd4e1c67a2d28fced849ee1bb76e7391b93eb12_this_i_s_the___file.txt'
     *      'path'      => '2/f/d/4/',
     *      'path_name' => '2/f/d/4/2fd4e1c67a2d28fced849ee1bb76e7391b93eb12_this_i_s_the___file.txt',
     *   ]
     *
     * @param \SplFileInfo $file
     *
     * @return array
     */
    public function generate(\SplFileInfo $file)
    {
        $originalFileName = ($file instanceof UploadedFile) ? $file->getClientOriginalName() : $file->getFilename();
        $uuid = $this->generateUuid($originalFileName);
        $sanitized = preg_replace('#[^A-Za-z0-9\.]#', '_', $originalFileName);

        if (strlen($sanitized) > 100) {
            $sanitized = sprintf('%s.%s', substr($sanitized, 0, 95), $file->getExtension());
        }

        $fileName = $uuid . '_' . $sanitized;
        $path = sprintf('%s/%s/%s/%s/', $uuid[0], $uuid[1], $uuid[2], $uuid[3]);
        $pathName = $path.$fileName;

        return [
            'uuid'      => $uuid,
            'file_name' => $fileName,
            'path'      => $path,
            'path_name' => $pathName,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function generateUuid($fileName)
    {
        return sha1($fileName . microtime());
    }
}
