<?php

namespace DamEnterprise\Component\Transformer\Transformation;

use DamEnterprise\Component\Transformer\Options\TransformationOptionsResolverInterface;

abstract class AbstractTransformation implements TransformationInterface
{
    /** @var array */
    protected $mimeTypes = [];

    /** @var TransformationOptionsResolverInterface */
    protected $optionsResolver;

    public function supportsMimeType($mimeType)
    {
        return in_array($mimeType, $this->mimeTypes);
    }

    public function getMimeTypes()
    {
        return $this->mimeTypes;
    }

    public function getOptionsResolver()
    {
        return $this->optionsResolver;
    }

    public function setOptionsResolver($optionsResolver)
    {
        $this->optionsResolver = $optionsResolver;

        return $this;
    }
}
