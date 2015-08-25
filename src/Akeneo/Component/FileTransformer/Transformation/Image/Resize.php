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
use Imagine\Image\Box;

/**
 * Transform the size of an image without scaling
 *
 * @author Julien Janvier <jjanvier@akeneo.com>
 */
class Resize extends AbstractTransformation
{
    /** @var ImagickLauncher */
    protected $launcher;

    /**
     * @param TransformationOptionsResolverInterface $optionsResolver
     * @param ImagickLauncher                        $launcher
     * @param array                                  $supportedMimeTypes
     */
    public function __construct(
        TransformationOptionsResolverInterface $optionsResolver,
        ImagickLauncher $launcher,
        array $supportedMimeTypes = ['image/jpeg', 'image/tiff', 'image/png']
    ) {
        $this->optionsResolver    = $optionsResolver;
        $this->supportedMimeTypes = $supportedMimeTypes;
        $this->launcher           = $launcher;
    }

    /**
     * $options =
     * [
     *      'width'  => int,
     *      'height' => int
     * ]
     *
     * {@inheritdoc}
     */
    public function transform(\SplFileInfo $file, array $options = [])
    {
        $options = $this->optionsResolver->resolve($options);

        $imagine = new Imagine();
        $image   = $imagine->open($file->getPathname());

        if ($options['width'] > $image->getSize()->getWidth()) {
            throw new ImageWidthException($file->getPathname(), $this->getName());
        } elseif ($options['height'] > $image->getSize()->getHeight()) {
            throw new ImageHeightException($file->getPathname(), $this->getName());
        }

        $this->launcher->convert(
            sprintf('-resize %dx%d', $options['width'], $options['height']),
            $file->getPathname()
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'resize';
    }
}
