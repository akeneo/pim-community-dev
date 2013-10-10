<?php

namespace Oro\Bundle\DataGridBundle\Extension\Formatter\Property;

use Symfony\Component\Routing\Router;
use Oro\Bundle\DataGridBundle\Extension\Formatter\ResultRecordInterface;

class UrlProperty extends AbstractProperty
{
    /**
     * @var Router
     */
    protected $router;

    public function init(array $params)
    {
        if (!isset($params['placeholders'])) {
            $params['placeholders'] = array();
        }
        parent::init($params);
    }


    /**
     * @param Router $router
     */
    public function __construct(Router $router)
    {
        $this->router = $router;
    }

    /**
     * {@inheritdoc}
     */
    public function getValue(ResultRecordInterface $record)
    {
        $route = $this->router->generate(
            $this->get('route'),
            $this->getParameters($record),
            $this->getOr('isAbsolute', false)
        );

        return $route . $this->getOr('anchor');
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
        $result = array();
        foreach ($this->getOr('params', array()) as $name => $dataKey) {
            if (is_numeric($name)) {
                $name = $dataKey;
            }
            $result[$name] = $record->getValue($dataKey);
        }

        return $result;
    }
}
