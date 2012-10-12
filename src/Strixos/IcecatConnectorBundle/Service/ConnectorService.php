<?php
namespace Strixos\IcecatConnectorBundle\Service;

/**
 * Connector service
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright Copyright (c) 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class ConnectorService
{
    // TODO extends abstract service

    protected $container;

    /**
     * Constructor
     * @param unknown_type $container
     */
    public function __construct($container)
    {
        $this->container = $container;
    }

    public function hello()
    {
        $type = $this->container->get('akeneo.catalog.model_producttype');
        var_dump($type);
    }
}