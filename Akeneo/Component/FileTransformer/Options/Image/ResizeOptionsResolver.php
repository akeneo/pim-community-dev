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
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * Option resolver for Resize transformation
 *
 * @author Julien Janvier <jjanvier@akeneo.com>
 */
class ResizeOptionsResolver implements TransformationOptionsResolverInterface
{
    /** @var OptionsResolverInterface */
    protected $resolver;

    public function __construct()
    {
        $this->resolver = new OptionsResolver();
        $this->resolver->setRequired(['width', 'height']);
        $this->resolver->setAllowedTypes(['width' => 'int', 'height' => 'int']);
    }

    /**
     * {@inheritdoc}
     */
    public function resolve(array $options)
    {
        try {
            $options = $this->resolver->resolve($options);
        } catch (\Exception $e) {
            throw InvalidOptionsTransformationException::general($e, 'resize');
        }

        return $options;
    }
}
