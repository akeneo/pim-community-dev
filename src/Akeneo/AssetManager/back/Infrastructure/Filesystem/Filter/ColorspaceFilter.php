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

namespace Akeneo\AssetManager\Infrastructure\Filesystem\Filter;

use Imagine\Image\ImageInterface;
use Imagine\Image\Palette\CMYK;
use Imagine\Image\Palette\Grayscale;
use Imagine\Image\Palette\RGB;
use Liip\ImagineBundle\Imagine\Filter\Loader\LoaderInterface;
use Webmozart\Assert\Assert;

class ColorspaceFilter implements LoaderInterface
{
    public function load(ImageInterface $image, array $options = [])
    {
        Assert::keyExists($options, 'colorspace');

        match ($options['colorspace']) {
            'grey' => $image->usePalette(new Grayscale()),
            'cmyk' => $image->usePalette(new CMYK()),
            default => $image->usePalette(new RGB()),
        };

        return $image;
    }
}
