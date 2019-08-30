<?php

namespace Akeneo\Platform\Bundle\UIBundle\Imagine;

use Akeneo\Tool\Component\FileStorage\FilesystemProvider;
use Akeneo\Tool\Component\FileStorage\Repository\FileInfoRepositoryInterface;
use Liip\ImagineBundle\Binary\Loader\LoaderInterface;
use Liip\ImagineBundle\Exception\Binary\Loader\NotLoadableException;
use Liip\ImagineBundle\Model\Binary;
use Symfony\Component\HttpFoundation\File\MimeType\MimeTypeGuesser;

/**
 * Image loader for Flysystem
 *
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FlysystemLoader implements LoaderInterface
{
    /** @var FilesystemProvider */
    protected $filesystemProvider;

    /** @var string */
    protected $filesystemAliases;

    /** @var FileInfoRepositoryInterface */
    protected $fileInfoRepository;

    public function __construct(
        FilesystemProvider $filesystemProvider,
        array $filesystemAliases,
        FileInfoRepositoryInterface $fileInfoRepository
    ) {
        $this->filesystemProvider = $filesystemProvider;
        $this->filesystemAliases = $filesystemAliases;
        $this->fileInfoRepository = $fileInfoRepository;
    }

    /**
     * {@inheritDoc}
     */
    public function find($path)
    {
        $content = $this->retrieveContentFileFromVfs($path);

        if (null === $content) {
            if (is_file($path)) {
                $content = $this->retrieveContentFileFromLocal($path);
            } else {
                throw new NotLoadableException(sprintf('Unable to read the file "%s" from the filesystem.', $path));
            }
        }

        return $content;
    }

    /**
     * Retrieve a file content from a virtual file system.
     * In case no filesystem has this file registered, null is returned.
     *
     * @param string $path
     *
     * @throws NotLoadableException
     *
     * @return Binary|null|string
     */
    protected function retrieveContentFileFromVfs($path)
    {
        $content = null;
        $mimeType = null;

        foreach ($this->filesystemAliases as $alias) {
            $fs = $this->filesystemProvider->getFilesystem($alias);
            if ($fs->has($path)) {
                //TODO: we should use readStream, the problem is that
                // \Liip\ImagineBundle\Model\Binary expects the full content...
                $content = $fs->read($path);
                $mimeType = $fs->getMimetype($path);
            }
        }

        if (null === $content) {
            // the path is not stored on any vfs
            return null;
        }

        if (false === $content) {
            throw new NotLoadableException(sprintf('Unable to read the file "%s" from the filesystem.', $path));
        }

        if ('application/octet-stream' === $mimeType) {
            $fileInfo = $this->fileInfoRepository->findOneByIdentifier($path);
            if (null !== $fileInfo) {
                $mimeType = $fileInfo->getMimetype();
            }
        }

        if (false === $mimeType || null === $mimeType) {
            return $content;
        }

        return new Binary($content, $mimeType);
    }

    /**
     * The file can have been stored locally, for example when we upload an image in the product
     * edit form, it is temporary stored in /tmp/pim/file_storage/xxx
     *
     * @param string $path
     *
     * @throws NotLoadableException
     *
     * @return Binary|string
     */
    protected function retrieveContentFileFromLocal($path)
    {
        $content = file_get_contents($path);
        $mimeType = MimeTypeGuesser::getInstance()->guess($path);

        if (false === $content) {
            throw new NotLoadableException(sprintf('Unable to read the file "%s".', $path));
        }

        if (null === $mimeType) {
            return $content;
        }

        return new Binary($content, $mimeType);
    }
}
