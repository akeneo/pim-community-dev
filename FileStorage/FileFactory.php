<?php

namespace Akeneo\Component\FileStorage;

use Symfony\Component\HttpFoundation\File\MimeType\MimeTypeGuesser;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * File factory, create a \Akeneo\Component\FileStorage\Model\FileInterface
 *
 * @author    Julien Janvier <jjanvier@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FileFactory implements FileFactoryInterface
{
    /** @var string */
    protected $fileClass;

    /**
     * @param string $fileClass
     */
    public function __construct($fileClass)
    {
        $this->fileClass = $fileClass;
    }

    /**
     * {@inheritdoc}
     */
    public function create(\SplFileInfo $rawFile, array $pathInfo, $destFsAlias)
    {
        if ($rawFile instanceof UploadedFile) {
            $size             = $rawFile->getClientSize();
            $mimeType         = $rawFile->getMimeType();
            $originalFilename = $rawFile->getClientOriginalName();
            $extension        = $rawFile->getClientOriginalExtension();
        } else {
            $size             = filesize($rawFile->getPathname());
            $mimeType         = MimeTypeGuesser::getInstance()->guess($rawFile->getPathname());
            $originalFilename = $rawFile->getFilename();
            $extension        = $rawFile->getExtension();
        }

        $file = new $this->fileClass();
        $file->setKey($pathInfo['path'] . $pathInfo['file_name']);
        $file->setGuid($pathInfo['guid']);
        $file->setMimeType($mimeType);
        $file->setOriginalFilename($originalFilename);
        $file->setSize($size);
        $file->setExtension($extension);
        $file->setStorage($destFsAlias);

        return $file;
    }
}
