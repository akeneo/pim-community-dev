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
        if (!$image instanceof Image) {
            return $image;
        }

        $dpi = $image->getImagick()->getImageResolution();
        $unit = $image->getImagick()->getImageUnits();

        $image->getImagick()->stripImage();

        if (false === $this->getKeepResolutionMetadataOption($options)) {
            return $image;
        }

        // PIM-11249: Metadata won't set if a different instance of Imagick is not made.
        $imageWithResolutionMetadata = new Image(
            (new \Imagick()),
            $image->palette(),
            $image->metadata()
        );
        $imageWithResolutionMetadata->getImagick()->readImageBlob($image->getImagick()->getImageBlob());
        $imageWithResolutionMetadata->getImagick()->setImageUnits($unit);
        $imageWithResolutionMetadata->getImagick()->setImageResolution($dpi['x'], $dpi['y']);

        return $imageWithResolutionMetadata;
    }

    private function getKeepResolutionMetadataOption(array $options): bool
    {
        return
            true === array_key_exists('keep_resolution_metadata', $options)
            && true === $options['keep_resolution_metadata'];
    }
}
