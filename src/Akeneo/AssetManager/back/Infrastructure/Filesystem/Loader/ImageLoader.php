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

use League\Flysystem\FilesystemInterface;
use Liip\ImagineBundle\Binary\Loader\LoaderInterface;
use Liip\ImagineBundle\Exception\Binary\Loader\NotLoadableException;
use Liip\ImagineBundle\Exception\InvalidArgumentException;
use Liip\ImagineBundle\Model\Binary;
use Symfony\Component\HttpFoundation\File\MimeType\ExtensionGuesserInterface as DeprecatedExtensionGuesserInterface;
use Symfony\Component\Mime\MimeTypesInterface;

/**
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * Copy and override of Liip\ImagineBundle\Binary\Loader\FlysystemLoader
 */
class ImageLoader implements LoaderInterface
{
    protected FilesystemInterface $filesystem;

    /**
     * @var MimeTypesInterface|DeprecatedExtensionGuesserInterface
     */
    protected $extensionGuesser;

    public function __construct(
        $extensionGuesser,
        FilesystemInterface $filesystem)
    {
        if (!$extensionGuesser instanceof MimeTypesInterface && !$extensionGuesser instanceof DeprecatedExtensionGuesserInterface) {
            throw new InvalidArgumentException('$extensionGuesser must be an instance of Symfony\Component\Mime\MimeTypesInterface or Symfony\Component\HttpFoundation\File\MimeType\ExtensionGuesserInterface');
        }

        if (interface_exists(MimeTypesInterface::class) && $extensionGuesser instanceof DeprecatedExtensionGuesserInterface) {
            @trigger_error(sprintf('Passing a %s to "%s()" is deprecated since Symfony 4.3, pass a "%s" instead.', DeprecatedExtensionGuesserInterface::class, __METHOD__, MimeTypesInterface::class), E_USER_DEPRECATED);
        }

        $this->extensionGuesser = $extensionGuesser;
        $this->filesystem = $filesystem;
    }

    /**
     * {@inheritdoc}
     */
    public function find($path)
    {
        if (!$this->filesystem->has($path)) {
            throw new NotLoadableException(sprintf('Source image "%s" not found.', $path));
        }

        $mimeType = $this->filesystem->getMimetype($path);
        $pathParts = pathinfo($path);

        // This is an override here to handle the google bucket case where images without extensions are considered as octet-stream
        if (!isset($pathParts['extension']) && $mimeType === 'application/octet-stream') {
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
        if ($this->extensionGuesser instanceof DeprecatedExtensionGuesserInterface) {
            return $this->extensionGuesser->guess($mimeType);
        }

        if (null === $mimeType) {
            return null;
        }

        return $this->extensionGuesser->getExtensions($mimeType)[0] ?? null;
    }
}
