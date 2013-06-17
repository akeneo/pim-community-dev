<?php

namespace Oro\Bundle\FormBundle\EntityAutocomplete;

interface SearchFactoryInterface
{
    /**
     * Creates search handler
     *
     * @param array $options
     * @return SearchHandlerInterface
     * @throws \RuntimeException When factory cannot create handler
     */
    public function create(array $options);
}
