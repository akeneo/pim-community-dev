<?php

namespace DamEnterprise\Component\Transformer\Options\Image;

use DamEnterprise\Component\Transformer\Exception\InvalidOptionsTransformationException;
use DamEnterprise\Component\Transformer\Options\TransformationOptionsResolverInterface;
use Imagine\Image\ImageInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

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
