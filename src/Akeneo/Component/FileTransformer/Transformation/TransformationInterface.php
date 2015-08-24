<?php

/**
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
 * @author Julien Janvier <jjanvier@akeneo.com>
 */
interface TransformationInterface
{
    /**
     * Apply a transformation on the given file with given options
     *
     * @param \SplFileInfo $file
     * @param array        $options
     *
     * @throws \Akeneo\Component\FileTransformer\Exception\NotApplicableTransformation\NotApplicableTransformationException
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
     * Is mime type supported by the transformation ?
     *
     * @param string $mimeType
     *
     * @return bool
     */
    public function isMimeTypeSupported($mimeType);

    /**
     * Returns mime types supported by the transformation
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
    public function setOptionsResolver(TransformationOptionsResolverInterface $optionsResolver);
}
