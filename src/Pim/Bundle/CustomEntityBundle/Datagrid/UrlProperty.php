<?php

namespace Pim\Bundle\CustomEntityBundle\Datagrid;

use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Oro\Bundle\GridBundle\Datagrid\ResultRecordInterface;
use Oro\Bundle\GridBundle\Property\UrlProperty as OroUrlProperty;

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
