<?php

namespace DamEnterprise\Component\Transformer\Options\Image;

use DamEnterprise\Component\Transformer\Exception\InvalidOptionsTransformationException;
use DamEnterprise\Component\Transformer\Options\TransformationOptionsResolverInterface;
use Imagine\Image\Palette\PaletteInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ColorSpaceOptionsResolver implements TransformationOptionsResolverInterface
{
    /** @var OptionsResolverInterface */
    protected $resolver;

    public function __construct()
    {
        $this->resolver = new OptionsResolver();
        $this->resolver->setRequired(['colorspace']);
        $this->resolver->setAllowedTypes(['colorspace' => 'string']);
        $this->resolver->setAllowedValues(
            [
                'colorspace' => [
                    PaletteInterface::PALETTE_CMYK,
                    PaletteInterface::PALETTE_RGB,
                    PaletteInterface::PALETTE_GRAYSCALE
                ]
            ]
        );
    }

    public function resolve(array $options)
    {
        try {
            $options = $this->resolver->resolve($options);
        } catch (\Exception $e) {
            throw InvalidOptionsTransformationException::general($e, 'colorspace');
        }

        return $options;
    }
}
