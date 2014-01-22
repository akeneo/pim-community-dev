<?php

namespace Pim\Bundle\CustomEntityBundle\Extension\Formatter\Property;

use Oro\Bundle\DataGridBundle\Datasource\ResultRecordInterface;
use Oro\Bundle\DataGridBundle\Extension\Formatter\Property\UrlProperty as OroUrlProperty;

/**
 * Overriden UrlProperty class
 *
 * @author    Antoine Guigan <antoine@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class UrlProperty extends OroUrlProperty
{
    /**
     * @var string
     */
    protected $customEntityName;

    /**
     * {@inheritdoc}
     */
    public function getRawValue(ResultRecordInterface $record)
    {
        $routeAndCustom = $this->get(self::ROUTE_KEY);
        preg_match('/(?P<routeName>\w+){customEntityName:(?P<customEntityName>\w+)}/', $routeAndCustom, $matches);
        $routeName = $matches['routeName'];
        $this->customEntityName = $matches['customEntityName'];

        $route = $this->router->generate(
            $routeName,
            $this->getParameters($record),
            $this->getOr(self::IS_ABSOLUTE_KEY, false)
        );

        return $route . $this->getOr(self::ANCHOR_KEY);
    }

    /**
     * {@inheritdoc}
     */
    protected function getParameters(ResultRecordInterface $record)
    {
        $result = parent::getParameters($record);
        $result['customEntityName'] = $this->customEntityName;

        return $result;
    }
}
