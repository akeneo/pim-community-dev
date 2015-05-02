<?php

namespace DamEnterprise\Component\Transformer\Transformation\Image;

use DamEnterprise\Component\Transformer\Exception\InvalidOptionsTransformationException;
use DamEnterprise\Component\Transformer\Transformation\AbstractTransformation;
use Imagine\Image\ImageInterface;
use Imagine\Imagick\Imagine;
use Symfony\Component\OptionsResolver\OptionsResolver;

//TODO: check this transformation
class Resolution extends AbstractTransformation
{
    public function __construct(array $mimeTypes = ['image/jpeg', 'image/tiff'])
    {
        $this->mimeTypes = $mimeTypes;
    }

    public function transform(\SplFileInfo $file, array $options = [])
    {
        $options = $this->checkOptions($options);

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

    protected function checkOptions(array $options)
    {
        $resolver = new OptionsResolver();
        $resolver
            ->setRequired(['resolution'])
            ->setOptional(['resolution-unit'])
            ->setAllowedTypes(['resolution' => 'int', 'resolution-unit' => 'string'])
            ->setDefaults(['resolution-unit' => ImageInterface::RESOLUTION_PIXELSPERINCH])
            ->setAllowedValues(
                [
                    'resolution-unit' => [
                        ImageInterface::RESOLUTION_PIXELSPERCENTIMETER,
                        ImageInterface::RESOLUTION_PIXELSPERINCH
                    ]
                ]
            );

        try {
            $options = $resolver->resolve($options);
        } catch (\Exception $e) {
            throw InvalidOptionsTransformationException::general($e, $this->getName());
        }

        return $options;
    }
}
