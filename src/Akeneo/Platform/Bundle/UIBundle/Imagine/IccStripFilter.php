<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2022 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Platform\Bundle\UIBundle\Imagine;

use Imagine\Image\ImageInterface;
use Imagine\Imagick\Image;
use Liip\ImagineBundle\Imagine\Filter\Loader\LoaderInterface;

/**
 * @TODO Remove this filter when imageMagick version is >= 6.9.11-61
 */
final class IccStripFilter implements LoaderInterface
{
    public function load(ImageInterface $image, array $options = []): ImageInterface
    {
        if ($image instanceof Image) {
            $image->getImagick()->stripImage();
        }

        return $image;
    }
}
