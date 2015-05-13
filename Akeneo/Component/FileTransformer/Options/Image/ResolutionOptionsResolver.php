<?php

/*
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
use Imagine\Image\ImageInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * Option resolver for Resolution transformation
 *
 * @author Willy Mesnage <willy.mesnage@akeneo.com>
 */
class ResolutionOptionsResolver implements TransformationOptionsResolverInterface
{
    /** @var OptionsResolverInterface */
    protected $resolver;

    public function __construct()
    {
        $this->resolver = new OptionsResolver();
        $this->resolver->setRequired(['resolution']);
        $this->resolver->setOptional(['resolution-unit']);
        $this->resolver->setAllowedTypes(['resolution' => 'int', 'resolution-unit' => 'string']);
        $this->resolver->setDefaults(['resolution-unit' => ImageInterface::RESOLUTION_PIXELSPERINCH]);
        $this->resolver->setAllowedValues(
            [
                'resolution-unit' => [
                    ImageInterface::RESOLUTION_PIXELSPERCENTIMETER,
                    ImageInterface::RESOLUTION_PIXELSPERINCH
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
            throw InvalidOptionsTransformationException::general($e, 'resolution');
        }

        return $options;
    }
}
