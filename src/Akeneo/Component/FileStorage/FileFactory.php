<?php

namespace Akeneo\Component\FileStorage;

use Akeneo\Component\FileStorage\Model\FileInterface;
use Symfony\Component\HttpFoundation\File\MimeType\MimeTypeGuesser;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * File factory, create a \Akeneo\Component\FileStorage\Model\FileInterface.
 *
 * @author    Julien Janvier <jjanvier@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FileFactory implements FileFactoryInterface
{
    /** @var PathGeneratorInterface */
    protected $pathGenerator;

    /** @var string */
    protected $fileClass;

    /**
     * @param PathGeneratorInterface    $pathGenerator
     * @param string                    $fileClass
     */
    public function __construct(PathGeneratorInterface $pathGenerator, $fileClass)
    {
        $this->pathGenerator = $pathGenerator;
        $this->fileClass = $fileClass;
    }

    /**
     * {@inheritdoc}
     */
    public function createFromRawFile(\SplFileInfo $rawFile, $destFsAlias)
    {
        $pathInfo = $this->pathGenerator->generate($rawFile);

        if ($rawFile instanceof UploadedFile) {
            $size = $rawFile->getClientSize();
            $mimeType = $rawFile->getMimeType();
            $originalFilename = $rawFile->getClientOriginalName();
            $extension = $rawFile->getClientOriginalExtension();
        } else {
            $size = filesize($rawFile->getPathname());
            $mimeType = MimeTypeGuesser::getInstance()->guess($rawFile->getPathname());
            $originalFilename = $rawFile->getFilename();
            $extension = $rawFile->getExtension();
        }

        $file = new $this->fileClass();
        $file->setKey($pathInfo['path'].$pathInfo['file_name']);
        $file->setUuid($pathInfo['uuid']);
        $file->setMimeType($mimeType);
        $file->setOriginalFilename($originalFilename);
        $file->setSize($size);
        $file->setExtension($extension);
        $file->setStorage($destFsAlias);

        return $file;
    }

    /**
     * {@inheritdoc}
     */
    public function createFromFile(FileInterface $file, $destFsAlias, $key = null)
    {
        $uuid = $this->pathGenerator->generateUuid($file->getOriginalFilename());
        $key = null !== $key ? $key : $file->getKey();

        $newFile = new $this->fileClass();
        $newFile->setUuid($uuid);
        $newFile->setMimeType($file->getMimeType());
        $newFile->setOriginalFilename($file->getOriginalFilename());
        $newFile->setSize($file->getSize());
        $newFile->setExtension($file->getExtension());
        $newFile->setStorage($destFsAlias);
        $newFile->setKey($key);

       return $newFile;
    }
}
