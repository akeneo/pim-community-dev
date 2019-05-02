<?php

namespace Akeneo\Tool\Component\FileStorage;

use Akeneo\Tool\Component\FileStorage\Model\FileInfoInterface;
use Symfony\Component\HttpFoundation\File\MimeType\MimeTypeGuesser;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * File factory, create a \Akeneo\Tool\Component\FileStorage\Model\FileInfoInterface.
 *
 * @author    Julien Janvier <jjanvier@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FileInfoFactory implements FileInfoFactoryInterface
{
    /** @var PathGeneratorInterface */
    protected $pathGenerator;

    /** @var string */
    protected $fileClass;

    /** @var FilesystemProvider */
    private $filesystemProvider;

    public function __construct(
        PathGeneratorInterface $pathGenerator,
        FilesystemProvider $filesystemProvider,
        $fileClass
    ) {
        $this->pathGenerator = $pathGenerator;
        $this->fileClass = $fileClass;
        $this->filesystemProvider = $filesystemProvider;
    }

    /**
     * {@inheritdoc}
     */
    public function createFromRawFile(\SplFileInfo $rawFile, $destFsAlias): FileInfoInterface
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

        $filesystem = $this->filesystemProvider->getFilesystem('pefTmpStorage');
        $metadata = $filesystem->getMetadata($rawFile->getPathname());
        $mimeType = $metadata['mimetype'];
        $size = $metadata['size'];

        $file = new $this->fileClass();
        $file->setKey($pathInfo['path'] . $pathInfo['file_name']);
        $file->setMimeType($mimeType);
        $file->setOriginalFilename($originalFilename);
        $file->setSize($size);
        $file->setExtension($extension);
        $file->setHash($sha1);
        $file->setStorage($destFsAlias);

        return $file;
    }
}
