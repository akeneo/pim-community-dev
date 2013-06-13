<?php

namespace Oro\Bundle\ImportExportBundle\ValueConverter;

class ValueConverterRegistry
{
    /**
     * @var ValueConverterInterface[]
     */
    protected $valueConverters;

    /**
     * @param ValueConverterInterface[] $valueConverters
     */
    public function __construct(array $valueConverters = array())
    {
        $this->valueConverters = $valueConverters;
    }

    /**
     * @param string $alias
     * @return ValueConverterInterface|null
     */
    public function getValueConverter($alias)
    {
        if (!empty($this->valueConverters[$alias])) {
            return $this->valueConverters[$alias];
        }

        return null;
    }
}
