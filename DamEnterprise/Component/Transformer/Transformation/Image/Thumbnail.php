<?php

namespace DamEnterprise\Component\Transformer\Transformation\Image;

use DamEnterprise\Component\Transformer\Options\TransformationOptionsResolverInterface;
use DamEnterprise\Component\Transformer\Transformation\AbstractTransformation;
use Imagine\Image\Box;
use Imagine\Imagick\Imagine;

class Thumbnail extends AbstractTransformation
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
        $thumbnail = $image->thumbnail(new Box($options['width'], $options['height']));
        $thumbnail->save($file->getPathname());
    }

    public function getName()
    {
        return 'thumbnail';
    }
}
