<?php

namespace DamEnterprise\Component\Transformer\Transformation\Image;

use DamEnterprise\Component\Transformer\Options\TransformationOptionsResolverInterface;
use DamEnterprise\Component\Transformer\Transformation\AbstractTransformation;
use Imagine\Imagick\Imagine;

//TODO: check this transformation
class Resolution extends AbstractTransformation
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

        $imagickOptions = [
            'resolution-x'     => $options['resolution'],
            'resolution-y'     => $options['resolution'],
            'resolution-units' => $options['resolution-unit'],
        ];

        $imagine = new Imagine();
        $image   = $imagine->open($file->getPathname());
        $image->save($file->getPathname(), $imagickOptions);
    }

    public function getName()
    {
        return 'resolution';
    }
}
