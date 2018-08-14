<?php

namespace Akeneo\Platform\Bundle\UIBundle\Form\Transformer;

interface TransformerFactoryInterface
{
    /**
     * Creates a transformer for the given options
     *
     * @param array $options
     *
     * @return \Symfony\Component\Form\DataTransformerInterface
     */
    public function create(array $options);
}
