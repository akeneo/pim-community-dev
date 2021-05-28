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
use Liip\ImagineBundle\Binary\BinaryInterface;
use Liip\ImagineBundle\Binary\Loader\LoaderInterface;
use Liip\ImagineBundle\Model\Binary;

/**
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 */
class PdfLoader implements LoaderInterface
{
    private LoaderInterface $loader;

    private DefaultImageProviderInterface $defaultImageProvider;

    public function __construct(
        LoaderInterface $streamLoader,
        DefaultImageProviderInterface $defaultImageProvider
    ) {
        $this->loader = $streamLoader;
        $this->defaultImageProvider = $defaultImageProvider;
    }

    public function find($path)
    {
        $file = $this->loader->find($path);

        $gsExists = !empty(shell_exec('which gs'));
        if (!$gsExists) {
            return $this->defaultImageProvider->getImageBinary(MediaLinkPdfGenerator::DEFAULT_IMAGE);
        }

        if ($file instanceof BinaryInterface) {
            $file = $file->getContent();
        }

        $imagick = new \Imagick();
        $imagick->readImageBlob($file);
        $imagick->setIteratorIndex(0);
        $imagick->setResolution(72, 72);
        $imagick->setImageFormat('png64');

        $content = $imagick->getImageBlob();
        $imagick->destroy();

        return new Binary($content, 'image/png', 'png');
    }
}
