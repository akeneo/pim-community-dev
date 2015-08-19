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
 * Allow to apply a transformation on a file
 *
 * @author Julien Janvier <jjanvier@akeneo.com>
 */
abstract class AbstractTransformation implements TransformationInterface
{
    /** @var array */
    protected $supportedMimeTypes = [];

    /** @var TransformationOptionsResolverInterface */
    protected $optionsResolver;

    /**
     * {@inheritdoc}
     */
    public function isMimeTypeSupported($mimeType)
    {
        return in_array($mimeType, $this->supportedMimeTypes);
    }

    /**
     * {@inheritdoc}
     */
    public function getSupportedMimeTypes()
    {
        return $this->supportedMimeTypes;
    }

    /**
     * {@inheritdoc}
     */
    public function getOptionsResolver()
    {
        return $this->optionsResolver;
    }

    /**
     * {@inheritdoc}
     */
    public function setOptionsResolver(TransformationOptionsResolverInterface $optionsResolver)
    {
        $this->optionsResolver = $optionsResolver;

        return $this;
    }
}
