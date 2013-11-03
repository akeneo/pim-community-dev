<?php

namespace Oro\Bundle\GridBundle\Sorter;

use Oro\Bundle\GridBundle\Field\FieldDescriptionInterface;

interface SorterFactoryInterface
{
    /**
     * @param FieldDescriptionInterface $field
     * @param string $direction
     *
     * @return mixed
     */
    public function create(FieldDescriptionInterface $field, $direction = null);
}
