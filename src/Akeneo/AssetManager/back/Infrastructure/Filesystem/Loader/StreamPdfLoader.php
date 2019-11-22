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

use Akeneo\AssetManager\Infrastructure\Filesystem\PreviewGenerator\DefaultImageProviderInterface;
use Akeneo\AssetManager\Infrastructure\Filesystem\PreviewGenerator\MediaLinkPdfGenerator;
use Liip\ImagineBundle\Binary\Loader\LoaderInterface;
use Liip\ImagineBundle\Model\Binary;

/**
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 */
class StreamPdfLoader implements LoaderInterface
{
    private $streamLoader;
    private $defaultImageProvider;

    public function __construct(
        LoaderInterface $streamLoader,
        DefaultImageProviderInterface $defaultImageProvider
    ) {
        $this->streamLoader = $streamLoader;
        $this->defaultImageProvider = $defaultImageProvider;
    }

    public function find($path)
    {
        $file = $this->streamLoader->find($path);

        $gsExists = !empty(shell_exec('which gs'));
        if (!$gsExists) {
            return $this->defaultImageProvider->getImageBinary(MediaLinkPdfGenerator::DEFAULT_IMAGE);
        }

        $imagick = new \Imagick();
        $imagick->readImageBlob($file);
        $imagick->setResolution(72, 72);
        $imagick->setColorspace(\Imagick::COLORSPACE_RGB);
        $imagick->setCompression(\Imagick::COMPRESSION_JPEG);
        $imagick->setCompressionQuality(60);
        $imagick->setImageFormat('jpeg');
        $imagick->resetIterator();

        $content = $imagick->getImageBlob();
        $imagick->destroy();

        return new Binary($content, 'image/jpeg', 'jpeg');
    }
}
