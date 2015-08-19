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

//TODO: check this transformation
/**
 * Transform resolution of an image
 *
 * @author Julien Janvier <jjanvier@akeneo.com>
 */
class Resolution extends AbstractTransformation
{
    /**
     * {@inheritdoc}
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
     *      'resolution'      => int,
     *      'resolution-unit' => 'ppc' or 'ppi' (optional)
     * ]
     *
     * {@inheritdoc}
     */
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

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'resolution';
    }
}
