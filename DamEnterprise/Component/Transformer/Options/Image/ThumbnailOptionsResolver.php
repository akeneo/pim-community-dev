<?php

namespace DamEnterprise\Component\Transformer\Options\Image;

use DamEnterprise\Component\Transformer\Exception\InvalidOptionsTransformationException;
use DamEnterprise\Component\Transformer\Options\TransformationOptionsResolverInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ThumbnailOptionsResolver implements TransformationOptionsResolverInterface
{
    /** @var OptionsResolverInterface */
    protected $resolver;

    public function __construct()
    {
        $this->resolver = new OptionsResolver();
        $this->resolver->setRequired(['width', 'height']);
        $this->resolver->setAllowedTypes(['width' => 'int', 'height' => 'int']);
    }

    public function resolve(array $options)
    {
        try {
            $options = $this->resolver->resolve($options);
        } catch (\Exception $e) {
            throw InvalidOptionsTransformationException::general($e, 'thumbnail');
        }

        return $options;
    }
}
