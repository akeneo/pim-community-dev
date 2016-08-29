<?php

namespace Oro\Bundle\DataGridBundle\Extension\Formatter\Property;

use Oro\Bundle\DataGridBundle\Datasource\ResultRecordInterface;
use Symfony\Component\Routing\Router;

class UrlProperty extends AbstractProperty
{
    const ROUTE_KEY = 'route';
    const IS_ABSOLUTE_KEY = 'isAbsolute';
    const ANCHOR_KEY = 'anchor';
    const PARAMS_KEY = 'params';

    /** @var array */
    protected $excludeParams = [self::ROUTE_KEY, self::IS_ABSOLUTE_KEY, self::ANCHOR_KEY, self::PARAMS_KEY];

    /**
     * @var Router
     */
    protected $router;

    public function __construct(Router $router)
    {
        $this->router = $router;
    }

    /**
     * {@inheritdoc}
     */
    public function getRawValue(ResultRecordInterface $record)
    {
        $route = $this->router->generate(
            $this->get(self::ROUTE_KEY),
            $this->getParameters($record),
            $this->getOr(self::IS_ABSOLUTE_KEY, false)
        );

        return $route . $this->getOr(self::ANCHOR_KEY);
    }

    /**
     * Get route parameters from record
     *
     * @param ResultRecordInterface $record
     *
     * @return array
     */
    protected function getParameters(ResultRecordInterface $record)
    {
        $result = [];
        foreach ($this->getOr(self::PARAMS_KEY, []) as $name => $dataKey) {
            if (is_numeric($name)) {
                $name = $dataKey;
            }
            $result[$name] = $record->getValue($dataKey);
        }

        return $result;
    }
}
