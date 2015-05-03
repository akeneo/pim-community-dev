<?php

namespace DamEnterprise\Component\Transformer\Transformation\Image;

use DamEnterprise\Component\Transformer\Options\TransformationOptionsResolverInterface;
use DamEnterprise\Component\Transformer\Transformation\AbstractTransformation;
use Imagine\Image\Palette\CMYK;
use Imagine\Image\Palette\Grayscale;
use Imagine\Image\Palette\PaletteInterface;
use Imagine\Image\Palette\RGB;
use Imagine\Imagick\Imagine;

class ColorSpace extends AbstractTransformation
{
    public function __construct(
        TransformationOptionsResolverInterface $optionsResolver,
        array $mimeTypes = ['image/jpeg', 'image/tiff']
    ) {
        $this->optionsResolver = $optionsResolver;
        $this->mimeTypes = $mimeTypes;
    }

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

    public function getName()
    {
        return 'colorspace';
    }
}
