<?php

declare(strict_types=1);

namespace Akeneo\Platform\Bundle\UIBundle\Imagine;

use Imagine\Image\ImageInterface;
use Imagine\Imagick\Image;
use Liip\ImagineBundle\Imagine\Filter\Loader\LoaderInterface;

final class GifFilter implements LoaderInterface
{
    public function load(ImageInterface $image, array $options = []): ImageInterface
    {
        if ($image instanceof Image) {
            $image->getImagick()->coalesceImages();
        }

        return $image;
    }
}
