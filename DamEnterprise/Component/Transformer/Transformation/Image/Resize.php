<?php

namespace DamEnterprise\Component\Transformer\Transformation\Image;

use DamEnterprise\Component\Transformer\Exception\NotApplicableTransformationException;
use DamEnterprise\Component\Transformer\Options\TransformationOptionsResolverInterface;
use DamEnterprise\Component\Transformer\Transformation\AbstractTransformation;
use Imagine\Image\Box;
use Imagine\Imagick\Imagine;

class Resize extends AbstractTransformation
{
    //TODO: the list of mimetypes is defined here
    // vendor/symfony/symfony/src/Symfony/Component/HttpFoundation/File/MimeType/MimeTypeExtensionGuesser.php
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

        if ($options['width'] > $image->getSize()->getWidth()) {
            throw NotApplicableTransformationException::imageWidthTooBig($file->getPathname(), $this->getName());
        } elseif ($options['height'] > $image->getSize()->getHeight()) {
            throw NotApplicableTransformationException::imageHeightTooBig($file->getPathname(), $this->getName());
        }

        $image->resize(new Box($options['width'], $options['height']));
        $image->save();
    }

    public function getName()
    {
        return 'resize';
    }
}
