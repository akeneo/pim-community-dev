<?php

namespace DamEnterprise\Component\Transformer\Transformation\Image;

use DamEnterprise\Component\Transformer\Exception\InvalidOptionsTransformationException;
use DamEnterprise\Component\Transformer\Transformation\AbstractTransformation;
use Imagine\Image\Box;
use Imagine\Imagick\Imagine;
use Symfony\Component\OptionsResolver\OptionsResolver;

class Thumbnail extends AbstractTransformation
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
        $thumbnail = $image->thumbnail(new Box($options['width'], $options['height']));
        $thumbnail->save($file->getPathname());
    }

    public function getName()
    {
        return 'thumbnail';
    }

    protected function checkOptions(array $options)
    {
        $resolver = new OptionsResolver();
        $resolver->setRequired(['width', 'height']);
        $resolver->setAllowedTypes(['width' => 'int', 'height' => 'int']);

        try {
            $options = $resolver->resolve($options);
        } catch (\Exception $e) {
            throw InvalidOptionsTransformationException::general($e, $this->getName());
        }

        return $options;
    }
}
