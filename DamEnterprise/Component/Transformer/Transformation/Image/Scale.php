<?php

namespace DamEnterprise\Component\Transformer\Transformation\Image;

use DamEnterprise\Component\Transformer\Exception\NotApplicableTransformationException;
use DamEnterprise\Component\Transformer\Options\TransformationOptionsResolverInterface;
use DamEnterprise\Component\Transformer\Transformation\AbstractTransformation;
use Imagine\Imagick\Imagine;

class Scale extends AbstractTransformation
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
        $box     = $image->getSize();
        $ratio   = $options['ratio'];
        $width   = $options['width'];
        $height  = $options['height'];

        if (null !== $ratio) {
            $box = $box->scale($ratio);
        } elseif (null !== $width) {
            if ($width > $image->getSize()->getWidth()) {
                throw NotApplicableTransformationException::imageWidthTooBig($file->getPathname(), $this->getName());
            }
            $box = $box->widen($width);
        } elseif (null !== $height) {
            if ($height > $image->getSize()->getHeight()) {
                throw NotApplicableTransformationException::imageHeightTooBig($file->getPathname(), $this->getName());
            }
            $box = $box->heighten($height);
        }

        $image->resize($box);
        $image->save();
    }

    public function getName()
    {
        return 'scale';
    }
}
