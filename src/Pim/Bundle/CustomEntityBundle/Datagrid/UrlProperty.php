<?php

namespace Pim\Bundle\CustomEntityBundle\Datagrid;

use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Oro\Bundle\GridBundle\Datagrid\ResultRecordInterface;
use Oro\Bundle\GridBundle\Property\UrlProperty as OroUrlProperty;

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
     * @param string $name
     * @param Router $router
     * @param string $routeName
     * @param string $customEntityName
     * @param array  $placeholders
     * @param bool   $isAbsolute
     * @param null   $anchor
     */
    public function __construct(
        $name,
        Router $router,
        $routeName,
        $customEntityName,
        array $placeholders = array(),
        $isAbsolute = false,
        $anchor = null
    ) {
        parent::__construct($name, $router, $routeName, $placeholders, $isAbsolute, $anchor);
        $this->customEntityName = $customEntityName;
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
