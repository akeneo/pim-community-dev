<?php

namespace Akeneo\Tool\Component\FileStorage;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Mime\MimeTypes;

/**
 * File factory, create a \Akeneo\Tool\Component\FileStorage\Model\FileInfoInterface.
 *
 * @author    Julien Janvier <jjanvier@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FileInfoFactory implements FileInfoFactoryInterface
{
    protected PathGeneratorInterface $pathGenerator;
    protected string $fileClass;
    protected MimeTypes $mimeTypes;

    public function __construct(PathGeneratorInterface $pathGenerator, string $fileClass, MimeTypes $mimeTypes)
    {
        $this->pathGenerator = $pathGenerator;
        $this->fileClass = $fileClass;
        $this->mimeTypes = $mimeTypes;
    }

    /**
     * {@inheritdoc}
     */
    public function createFromRawFile(\SplFileInfo $rawFile, $destFsAlias)
    {
        $pathInfo = $this->pathGenerator->generate($rawFile);
        $sha1 = sha1_file($rawFile->getPathname());

        if ($rawFile instanceof UploadedFile) {
            $originalFilename = $rawFile->getClientOriginalName();
            $extension = $rawFile->getClientOriginalExtension();
        } else {
            $originalFilename = $rawFile->getFilename();
            $extension = $rawFile->getExtension();
        }

        $size = filesize($rawFile->getPathname());
        $mimeType = $this->mimeTypes->guessMimeType($rawFile->getPathname());

        $file = new $this->fileClass();
        $file->setKey($pathInfo['path'].$pathInfo['file_name']);
        $file->setMimeType($mimeType);
        $file->setOriginalFilename($originalFilename);
        $file->setSize($size);
        $file->setExtension($extension);
        $file->setHash($sha1);
        $file->setStorage($destFsAlias);

        return $file;
    }
}
