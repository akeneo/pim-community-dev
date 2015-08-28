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

use Akeneo\Component\FileTransformer\Options\TransformationOptionsResolverInterface;
use Akeneo\Component\FileTransformer\Transformation\AbstractTransformation;
use Imagine\Image\Box;
use Imagine\Imagick\Imagine;

/**
 * Transform an image to a thumbnail
 *
 * @author Julien Janvier <jjanvier@akeneo.com>
 */
class Thumbnail extends AbstractTransformation
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

        $this->launcher->convert(
            sprintf('-thumbnail %dx%d^', $options['width'], $options['height']),
            $file->getPathname()
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'thumbnail';
    }
}
