<?php

/**
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Component\FileTransformer\Options\Image;

use Akeneo\Component\FileTransformer\Exception\InvalidOptionsTransformationException;
use Akeneo\Component\FileTransformer\Options\TransformationOptionsResolverInterface;
use Imagine\Image\Palette\PaletteInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Option resolver for ColorSpace transformation
 *
 * @author Julien Janvier <jjanvier@akeneo.com>
 */
class ColorSpaceOptionsResolver implements TransformationOptionsResolverInterface
{
    /** @var OptionsResolver */
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

    /**
     * {@inheritdoc}
     */
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
