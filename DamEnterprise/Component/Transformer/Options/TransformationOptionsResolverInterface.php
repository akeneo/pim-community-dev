<?php

namespace DamEnterprise\Component\Transformer\Options;

use DamEnterprise\Component\Transformer\Exception\InvalidOptionsTransformationException;

interface TransformationOptionsResolverInterface
{
    /**
     * Returns the combination of the default and the passed options.
     *
     * @param array $options The custom option values.
     *
     * @return array A list of options and their values.
     * @throws InvalidOptionsTransformationException
     */
    public function resolve(array $options);
}
