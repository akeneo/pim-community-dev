<?php

namespace DamEnterprise\Component\Transformer\Transformation;

abstract class AbstractTransformation implements TransformationInterface
{
    /** @var array */
    protected $mimeTypes = [];

    public function supportsMimeType($mimeType)
    {
        return in_array($mimeType, $this->mimeTypes);
    }

    public function getMimeTypes()
    {
        return $this->mimeTypes;
    }
}
