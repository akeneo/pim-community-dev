<?php

declare(strict_types=1);

namespace Akeneo\Platform\Bundle\UIBundle\Imagine;

use Imagine\Image\ImageInterface;
use Imagine\Imagick\Image;
use Liip\ImagineBundle\Imagine\Filter\Loader\LoaderInterface;

final class FlattenLayersFilter implements LoaderInterface
{
    public function load(ImageInterface $image, array $options = []): ImageInterface
    {
        if ($image instanceof Image) {
            $image->getImagick()->mergeImageLayers(\Imagick::LAYERMETHOD_FLATTEN);
        }

        return $image;
    }
}
