<?php

/**
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Component\FileTransformer\Transformation\Image;

use Akeneo\Component\FileTransformer\Options\TransformationOptionsResolverInterface;
use Akeneo\Component\FileTransformer\Transformation\AbstractTransformation;
use Imagine\Image\Palette\CMYK;
use Imagine\Image\Palette\Grayscale;
use Imagine\Image\Palette\PaletteInterface;
use Imagine\Image\Palette\RGB;
use Imagine\Imagick\Imagine;

/**
 * Transform the color space of an image
 *
 * @author Julien Janvier <jjanvier@akeneo.com>
 */
class ColorSpace extends AbstractTransformation
{
    /**
     * @param TransformationOptionsResolverInterface $optionsResolver
     * @param array                                  $supportedMimeTypes
     */
    public function __construct(
        TransformationOptionsResolverInterface $optionsResolver,
        array $supportedMimeTypes = ['image/jpeg', 'image/tiff', 'image/png']
    ) {
        $this->optionsResolver    = $optionsResolver;
        $this->supportedMimeTypes = $supportedMimeTypes;
    }

    /**
     * $options = ['colorspace' => 'cmyk' or 'rgb' or 'gray']
     *
     * {@inheritdoc}
     */
    public function transform(\SplFileInfo $file, array $options = [])
    {
        $options = $this->optionsResolver->resolve($options);

        $imagine = new Imagine();
        $image   = $imagine->open($file->getPathname());

        if ($options['colorspace'] !== $image->palette()->name()) {
            switch ($options['colorspace']) {
                case PaletteInterface::PALETTE_CMYK:
                    $palette = new CMYK();
                    break;
                case PaletteInterface::PALETTE_RGB:
                    $palette = new RGB();
                    break;
                default:
                    $palette = new Grayscale();
                    break;
            }

            $image->usePalette($palette);
            $image->save();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'colorspace';
    }
}
