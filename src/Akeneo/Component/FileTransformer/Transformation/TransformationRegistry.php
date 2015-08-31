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

use Akeneo\Component\FileTransformer\Exception\AlreadyRegisteredTransformationException;
use Akeneo\Component\FileTransformer\Exception\NonRegisteredTransformationException;

/**
 * Registry for transformations
 *
 * @author Julien Janvier <jjanvier@akeneo.com>
 */
class TransformationRegistry
{
    /** @var TransformationInterface[] */
    protected $transformations = [];

    /**
     * Allows to retrieve all registered transformations
     *
     * @return TransformationInterface[]
     */
    public function all()
    {
        return $this->transformations;
    }

    /**
     * Add a transformation to the registry
     *
     * @param TransformationInterface $transformation
     *
     * @throws AlreadyRegisteredTransformationException
     *
     * @return TransformationRegistry
     */
    public function add(TransformationInterface $transformation)
    {
        $name = $transformation->getName();
        foreach ($transformation->getSupportedMimeTypes() as $mimeType) {
            if ($this->has($name, $mimeType)) {
                throw new AlreadyRegisteredTransformationException(
                    sprintf('Transformation "%s" already registered for the mime type. "%s"', $name, $mimeType)
                );
            }

            $key = $this->getTransformationKey($name, $mimeType);
            $this->transformations[$key] = $transformation;
        }

        return $this;
    }

    /**
     * Allows to retrieve a specific transformation
     *
     * @param string $name
     * @param string $mimeType
     *
     * @throws NonRegisteredTransformationException
     *
     * @return TransformationInterface
     */
    public function get($name, $mimeType)
    {
        $key = $this->getTransformationKey($name, $mimeType);
        if (isset($this->transformations[$key])) {
            return $this->transformations[$key];
        }

        throw new NonRegisteredTransformationException(
            $name,
            $mimeType,
            sprintf('No "%s" transformation registered for the mime type "%s".', $name, $mimeType)
        );
    }

    /**
     * Return whether or not a transformation is registered with the given $name and $mimeType
     *
     * @param string $name
     * @param string $mimeType
     *
     * @return bool
     */
    public function has($name, $mimeType)
    {
        $key = $this->getTransformationKey($name, $mimeType);

        return isset($this->transformations[$key]);
    }

    /**
     * Returns the transformation key used in the registry
     *
     * @param string $name
     * @param string $mimeType
     *
     * @return string
     */
    private function getTransformationKey($name, $mimeType)
    {
        return sprintf('%s-%s', $name, preg_replace('#[^A-Za-z0-9\.]#', '-', $mimeType));
    }
}
