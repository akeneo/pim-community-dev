<?php

namespace DamEnterprise\Component\Asset\Storage;

use Symfony\Component\HttpFoundation\File\UploadedFile;

class PathGenerator
{
    /**
     * @param \SplFileInfo $file
     *
     * @return array
     *   [
     *      'guid'      => '2fd4e1c67a2d28fced849ee1bb76e7391b93eb12',
     *      'file_name' => '2fd4e1c67a2d28fced849ee1bb76e7391b93eb12_this_is_the_file.txt'
     *      'path'      => '2f/d4/',
     *      'path_name' => '2f/d4/2fd4e1c67a2d28fced849ee1bb76e7391b93eb12_this_is_the_file.txt',
     *   ]
     */
    public function generate(\SplFileInfo $file)
    {
        $originalFileName = ($file instanceof UploadedFile) ? $file->getClientOriginalName() : $file->getFilename();

        $guid      = $this->generateId($file->getPathname());
        $sanitized = preg_replace('#[^A-Za-z0-9\.]#', '_', $originalFileName);
        $fileName  = $guid . '_' . $sanitized;
        $path      = sprintf('%s/%s/', substr($guid, 0, 2), substr($guid, 2, 2));
        $pathName  = $path . $fileName;

        return [
            'guid'      => $guid,
            'file_name' => $fileName,
            'path'      => $path,
            'path_name' => $pathName,
        ];
    }

    /**
     * @param string $pathName
     *
     * @return string
     */
    protected function generateId($pathName)
    {
        return sha1_file($pathName);
    }
}
