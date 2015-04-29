<?php

namespace DamEnterprise\Component\Transformer\Transformation;

use DamEnterprise\Component\Transformer\Exception\AlreadyRegisteredTransformationException;
use DamEnterprise\Component\Transformer\Exception\NonRegisteredTransformationException;

class TransformationRegistry
{
    /** @var TransformationInterface[] */
    protected $transformations = [];

    /**
     * @return TransformationInterface[]
     */
    public function all()
    {
        return $this->transformations;
    }

    /**
     * @param TransformationInterface $transformation
     *
     * @return TransformationRegistry
     * @throws AlreadyRegisteredTransformationException
     */
    public function add(TransformationInterface $transformation)
    {
        $name = $transformation->getName();
        foreach ($transformation->getMimeTypes() as $mimeType) {
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
     * @param string $name
     * @param string $mimeType
     *
     * @return TransformationInterface
     * @throws NonRegisteredTransformationException
     */
    public function get($name, $mimeType)
    {
        $key = $this->getTransformationKey($name, $mimeType);
        if (isset($this->transformations[$key])) {
            return $this->transformations[$key];
        }

        throw new NonRegisteredTransformationException(
            sprintf('No "%s" transformation registered for the mime type "%s".', $name, $mimeType)
        );
    }

    /**
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
