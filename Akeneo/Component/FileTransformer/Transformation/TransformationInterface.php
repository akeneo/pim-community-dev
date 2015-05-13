<?php

/*
* This file is part of the Akeneo PIM Enterprise Edition.
*
* (c) 2015 Akeneo SAS (http://www.akeneo.com)
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Akeneo\Component\FileTransformer\Transformation;

use Akeneo\Component\FileTransformer\Options\TransformationOptionsResolverInterface;

/**
 * Transformation interface
 *
 * @author Willy Mesnage <willy.mesnage@akeneo.com>
 */
interface TransformationInterface
{
    /**
     * Apply a transformation on the given file with given options
     *
     * @param \SplFileInfo $file
     * @param array        $options
     *
     * @throws \Akeneo\Component\FileTransformer\Exception\NotApplicableTransformationException
     *
     * @return null
     */
    public function transform(\SplFileInfo $file, array $options = []);

    /**
     * Get transformation name
     *
     * @return string
     */
    public function getName();

    /**
     * Is Myme Type supported by the transformation ?
     *
     * @param string $mimeType
     *
     * @return bool
     */
    public function isMimeTypeSupported($mimeType);

    /**
     * Returns Myme Types supported by the transformation
     *
     * @return array
     */
    public function getSupportedMimeTypes();

    /**
     * @return TransformationOptionsResolverInterface
     */
    public function getOptionsResolver();

    /**
     * @param TransformationOptionsResolverInterface $optionsResolver
     *
     * @return TransformationInterface
     */
    public function setOptionsResolver($optionsResolver);
}
