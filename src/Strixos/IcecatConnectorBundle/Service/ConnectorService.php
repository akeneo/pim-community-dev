<?php
namespace Strixos\IcecatConnectorBundle\Service;

use Strixos\IcecatConnectorBundle\Extract\LanguagesExtract;
use Strixos\IcecatConnectorBundle\Transform\LanguagesTransform;

use Strixos\DataFlowBundle\Model\Service\AbstractService;

use Strixos\IcecatConnectorBundle\Extract\SuppliersExtract;
use Strixos\IcecatConnectorBundle\Transform\SuppliersTransform;
use Strixos\IcecatConnectorBundle\Load\EntityLoad;

/**
 * Connector service
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright Copyright (c) 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class ConnectorService extends AbstractService
{
    // TODO extends abstract service

    protected $container;

    /**
     * Constructor
     * @param ContainerInterface $container
     */
    public function __construct($container)
    {
        $this->container = $container;
    }
    
    public function importSuppliers()
    {
    	$extract = new SuppliersExtract();
    	$extract->process();
    	
    	$loader = new EntityLoad($this->container->get('doctrine.orm.entity_manager'));
    	$transform = new SuppliersTransform($loader);
    	$transform->process();
    }
    
    public function importLanguages()
    {
    	$extract = new LanguagesExtract();
    	$extract->process();
    	
    	$loader = new EntityLoad($this->container->get('doctrine.orm.entity_manager'));
    	$transform = new LanguagesTransform($loader);
    	$transform->process();
    }
    
    public function importProducts()
    {
    	
    }
    
    public function importProduct($productId)
    {
    	
    }
}