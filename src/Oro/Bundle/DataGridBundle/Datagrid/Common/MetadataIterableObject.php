<?php

namespace Oro\Bundle\DataGridBundle\Datagrid\Common;

use Oro\Bundle\DataGridBundle\Common\IterableObject;

class MetadataIterableObject extends IterableObject
{
    public const GRID_NAME_KEY = 'gridName';
    public const OPTIONS_KEY = 'options';
    public const REQUIRED_MODULES_KEY = 'requireJSModules';

    /**
     * Default metadata array
     *
     * @return array
     */
    protected static function getDefaultMetadata()
    {
        return [
            self::REQUIRED_MODULES_KEY => [],
            self::OPTIONS_KEY          => []
        ];
    }

    /**
     * {@inheritDoc}
     */
    public static function createNamed($name, array $params)
    {
        $params = self::getDefaultMetadata();
        $params[self::OPTIONS_KEY][self::GRID_NAME_KEY] = $name;

        return self::create($params);
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        if (!isset($this[self::OPTIONS_KEY][self::GRID_NAME_KEY])) {
            throw new \LogicException("Trying to get name of unnamed object");
        }

        return $this[self::OPTIONS_KEY][self::GRID_NAME_KEY];
    }
}
