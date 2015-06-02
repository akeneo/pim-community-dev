<?php

/**
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Component\FileTransformer\Transformation\Image;

use Akeneo\Component\FileTransformer\Exception\NotApplicableTransformationException;
use Akeneo\Component\FileTransformer\Options\TransformationOptionsResolverInterface;
use Akeneo\Component\FileTransformer\Transformation\AbstractTransformation;
use Imagine\Imagick\Imagine;

/**
 * Transform the size of an image with scale
 *
 * @author Julien Janvier <jjanvier@akeneo.com>
 */
class Scale extends AbstractTransformation
{
    /**
     * @param TransformationOptionsResolverInterface $optionsResolver
     * @param array                                  $supportedMimeTypes
     */
    public function __construct(
        TransformationOptionsResolverInterface $optionsResolver,
        array $supportedMimeTypes = ['image/jpeg', 'image/tiff', 'image/png']
    ) {
        $this->optionsResolver    = $optionsResolver;
        $this->supportedMimeTypes = $supportedMimeTypes;
    }

    /**
     * $options =
     * [
     *      'ratio'  => float|null (optional)
     *      'width'  => int|null   (optional)
     *      'height' => int|null   (optional)
     * ]
     *
     * {@inheritdoc}
     */
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

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'scale';
    }
}
