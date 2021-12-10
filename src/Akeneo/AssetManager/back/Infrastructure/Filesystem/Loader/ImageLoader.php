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
use Liip\ImagineBundle\Exception\InvalidArgumentException;
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

        $mimeType = $this->filesystem->mimeType($path);
        $pathExtension = pathinfo($path, PATHINFO_EXTENSION);

        // This is an override here to handle the google bucket case where images without extensions are considered as octet-stream
        if (empty($pathExtension) && $mimeType === 'application/octet-stream') {
            $mimeType = 'image/jpeg';
        }
        $extension = $this->getExtension($mimeType);

        return new Binary(
            $this->filesystem->read($path),
            $mimeType,
            $extension
        );
    }

    private function getExtension(?string $mimeType): ?string
    {
        if (null === $mimeType) {
            return null;
        }

        return $this->extensionGuesser->getExtensions($mimeType)[0] ?? null;
    }
}
