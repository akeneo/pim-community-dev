<?php

namespace DamEnterprise\Component\Transformer\Transformation\Image;

use DamEnterprise\Component\Transformer\Exception\InvalidOptionsTransformationException;
use DamEnterprise\Component\Transformer\Transformation\AbstractTransformation;
use Imagine\Image\Palette\CMYK;
use Imagine\Image\Palette\Grayscale;
use Imagine\Image\Palette\PaletteInterface;
use Imagine\Image\Palette\RGB;
use Imagine\Imagick\Imagine;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ColorSpace extends AbstractTransformation
{
    public function __construct(array $mimeTypes = ['image/jpeg', 'image/tiff'])
    {
        $this->mimeTypes = $mimeTypes;
    }


    public function transform(\SplFileInfo $file, array $options = [])
    {
        $options = $this->checkOptions($options);

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

    protected function checkOptions(array $options)
    {
        $resolver = new OptionsResolver();
        $resolver->setRequired(['colorspace']);
        $resolver->setAllowedTypes(['colorspace' => 'string']);
        $resolver->setAllowedValues(
            [
                'colorspace' => [
                    PaletteInterface::PALETTE_CMYK,
                    PaletteInterface::PALETTE_RGB,
                    PaletteInterface::PALETTE_GRAYSCALE
                ]
            ]
        );

        try {
            $options = $resolver->resolve($options);
        } catch (\Exception $e) {
            throw InvalidOptionsTransformationException::general($e, $this->getName());
        }

        return $options;
    }
}
