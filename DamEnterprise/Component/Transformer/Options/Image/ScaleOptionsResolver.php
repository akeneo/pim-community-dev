<?php

namespace DamEnterprise\Component\Transformer\Options\Image;

use DamEnterprise\Component\Transformer\Exception\InvalidOptionsTransformationException;
use DamEnterprise\Component\Transformer\Options\TransformationOptionsResolverInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ScaleOptionsResolver implements TransformationOptionsResolverInterface
{
    /** @var OptionsResolverInterface */
    protected $resolver;

    public function __construct()
    {
        $this->resolver = new OptionsResolver();
        $this->resolver->setOptional(['ratio', 'width', 'height']);
        $this->resolver->setAllowedTypes(
            ['ratio' => ['float', 'null'], 'width' => ['int', 'null'], 'height' => ['int', 'null']]
        );
        $this->resolver->setDefaults(['ratio' => null, 'width' => null, 'height' => null]);
    }

    public function resolve(array $options)
    {
        try {
            $options = $this->resolver->resolve($options);
        } catch (\Exception $e) {
            throw InvalidOptionsTransformationException::general($e, 'scale');
        }

        $ratio  = $options['ratio'];
        $width  = $options['width'];
        $height = $options['height'];

        if (null === $ratio && null === $width && null === $height) {
            throw InvalidOptionsTransformationException::chooseOneOption(['ratio', 'width', 'height'], 'scale');
        }

        if (null !== $ratio && ($ratio <= 0 || $ratio >= 1)) {
            throw InvalidOptionsTransformationException::ratio('ratio', 'scale');
        }

        return $options;
    }
}
