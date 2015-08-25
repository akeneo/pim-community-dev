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
use Imagine\Imagick\Imagine;

/**
 * Transform resolution of an image
 *
 * @author Julien Janvier <jjanvier@akeneo.com>
 */
class Resolution extends AbstractTransformation
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
     *      'resolution'      => int,
     *      'resolution-unit' => 'ppc' or 'ppi' (optional)
     * ]
     *
     * {@inheritdoc}
     */
    public function transform(\SplFileInfo $file, array $options = [])
    {
        $options = $this->optionsResolver->resolve($options);

        $unit = 'PixelsPerInch';
        if ('ppc' === $options['resolution-unit']) {
            $unit = 'PixelsPerCentimeter';
        }

        $this->launcher->convert(
            sprintf('-density %dx%d -units %s', $options['resolution'], $options['resolution'], $unit),
            $file->getPathname()
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'resolution';
    }
}
