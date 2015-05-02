<?php

namespace DamEnterprise\Component\Transformer\Transformation\Image;

use DamEnterprise\Component\Transformer\Exception\InvalidOptionsTransformationException;
use DamEnterprise\Component\Transformer\Exception\NotApplicableTransformationException;
use DamEnterprise\Component\Transformer\Transformation\AbstractTransformation;
use Imagine\Image\Box;
use Imagine\Imagick\Imagine;
use Symfony\Component\OptionsResolver\OptionsResolver;

class Scale extends AbstractTransformation
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

    protected function checkOptions(array $options)
    {
        $resolver = new OptionsResolver();
        $resolver->setOptional(['ratio', 'width', 'height']);
        $resolver->setAllowedTypes(
            ['ratio' => ['float', 'null'], 'width' => ['int', 'null'], 'height' => ['int', 'null']]
        );
        $resolver->setDefaults(['ratio' => null, 'width' => null, 'height' => null]);

        try {
            $options = $resolver->resolve($options);
        } catch (\Exception $e) {
            throw InvalidOptionsTransformationException::general($e, $this->getName());
        }

        $ratio  = $options['ratio'];
        $width  = $options['width'];
        $height = $options['height'];

        if (null === $ratio && null === $width && null === $height) {
            throw InvalidOptionsTransformationException::chooseOneOption(
                ['ratio', 'width', 'height'],
                $this->getName()
            );
        }

        if (null !== $ratio && ($ratio <= 0 || $ratio >= 1)) {
            throw InvalidOptionsTransformationException::ratio('ratio', $this->getName());
        }

        return $options;
    }
}
