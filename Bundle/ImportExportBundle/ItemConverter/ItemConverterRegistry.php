<?php

namespace Oro\Bundle\ImportExportBundle\ItemConverter;

class ItemConverterRegistry
{
    /**
     * @var ItemConverterInterface[]
     */
    protected $itemConverters;

    /**
     * @param ItemConverterInterface[] $itemConverters
     */
    public function __construct(array $itemConverters = array())
    {
        $this->itemConverters = $itemConverters;
    }

    /**
     * @param string $alias
     * @return ItemConverterInterface|null
     */
    public function getItemConverter($alias)
    {
        if (!empty($this->itemConverters[$alias])) {
            return $this->itemConverters[$alias];
        }

        return null;
    }
}
