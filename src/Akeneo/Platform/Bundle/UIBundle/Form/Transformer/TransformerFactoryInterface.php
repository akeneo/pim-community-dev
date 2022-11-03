<?php

namespace Akeneo\Platform\Bundle\UIBundle\Form\Transformer;

use Symfony\Component\Form\DataTransformerInterface;

interface TransformerFactoryInterface
{
    /**
     * Creates a transformer for the given options
     *
     * @param array $options
     *
     * @return DataTransformerInterface
     */
    public function create(array $options);
}
