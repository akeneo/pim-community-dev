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

use Akeneo\Component\FileTransformer\Exception\NotApplicableTransformation\ImageHeightException;
use Akeneo\Component\FileTransformer\Exception\NotApplicableTransformation\ImageWidthException;
use Akeneo\Component\FileTransformer\Options\TransformationOptionsResolverInterface;
use Akeneo\Component\FileTransformer\Transformation\AbstractTransformation;
use Imagine\Gd\Imagine;

/**
 * Transform the size of an image with scale
 *
 * @author Julien Janvier <jjanvier@akeneo.com>
 */
class Scale extends AbstractTransformation
{
    /** @var ImageMagickLauncher */
    protected $launcher;

    /**
     * @param TransformationOptionsResolverInterface $optionsResolver
     * @param ImageMagickLauncher                    $launcher
     * @param array                                  $supportedMimeTypes
     */
    public function __construct(
        TransformationOptionsResolverInterface $optionsResolver,
        ImageMagickLauncher $launcher,
        array $supportedMimeTypes = [
            'image/jpeg',
            'image/tiff',
            'image/png'
        ]
    ) {
        $this->optionsResolver    = $optionsResolver;
        $this->supportedMimeTypes = $supportedMimeTypes;
        $this->launcher           = $launcher;
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
        $ratio   = $options['ratio'];
        $width   = $options['width'];
        $height  = $options['height'];

        if (null !== $ratio) {
            $command = sprintf('-scale %d%%', $ratio);
        } elseif (null !== $width) {
            if ($width > $image->getSize()->getWidth()) {
                throw new ImageWidthException($file->getPathname(), $this->getName());
            }
            $command = sprintf('-scale %d', $width);
        } else {
            if ($height > $image->getSize()->getHeight()) {
                throw new ImageHeightException($file->getPathname(), $this->getName());
            }
            $command = sprintf('-scale x%d', $height);
        }

        $this->launcher->convert($command, $file->getPathname());
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'scale';
    }
}
