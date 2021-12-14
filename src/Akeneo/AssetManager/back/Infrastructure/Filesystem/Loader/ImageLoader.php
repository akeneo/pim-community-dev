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

namespace Akeneo\AssetManager\Infrastructure\Filesystem\Loader;

use League\Flysystem\FilesystemReader;
use Liip\ImagineBundle\Binary\Loader\LoaderInterface;
use Liip\ImagineBundle\Exception\Binary\Loader\NotLoadableException;
use Liip\ImagineBundle\Model\Binary;
use Symfony\Component\Mime\MimeTypesInterface;

/**
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * Copy and override of Liip\ImagineBundle\Binary\Loader\FlysystemLoader
 */
class ImageLoader implements LoaderInterface
{
    protected FilesystemReader $filesystem;
    protected MimeTypesInterface $extensionGuesser;

    public function __construct(MimeTypesInterface $extensionGuesser, FilesystemReader $filesystem)
    {
        $this->extensionGuesser = $extensionGuesser;
        $this->filesystem = $filesystem;
    }

    /**
     * {@inheritdoc}
     */
    public function find($path)
    {
        if (false === $this->filesystem->fileExists($path)) {
            throw new NotLoadableException(sprintf('Source image "%s" not found.', $path));
        }

        $mimeType = $this->getMimeType($path);
        $extension = $this->getExtension($mimeType);

        return new Binary(
            $this->filesystem->read($path),
            $mimeType,
            $extension
        );
    }

    private function getMimeType(string $path): string
    {
        $mimeType = $this->filesystem->mimeType($path);

        // Dirty fix for PIM-10195 until https://github.com/thephpleague/flysystem/pull/1299 is merged
        // `Flysystem\GoogleCloudStorageAdapter` does not transmit the content type of the file
        // Without content type, it's up to `GuzzleHttp\Psr7\MimeType` to guess the mime type
        if ('application/postscript' === $mimeType) {
            return 'image/x-eps';
        }

        // This is an override here to handle the google bucket case where images without extensions are considered as octet-stream
        $pathExtension = pathinfo($path, PATHINFO_EXTENSION);
        if (empty($pathExtension) && $mimeType === 'application/octet-stream') {
            return 'image/jpeg';
        }

        return $mimeType;
    }

    private function getExtension(?string $mimeType): ?string
    {
        if (null === $mimeType) {
            return null;
        }

        return $this->extensionGuesser->getExtensions($mimeType)[0] ?? null;
    }
}
