<?php
namespace Strixos\IcecatConnectorBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
/**
 * 
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright Copyright (c) 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class Configs
{
    /**
     * @var ArrayCollection
     */
    protected $configs;
    
    /**
     * Constructor for Config collection
     * 
     * @param array $configs
     */
    public function __construct(array $configs = array())
    {
        $this->configs = new ArrayCollection($configs);
    }
    
    /**
     * Add a Config entity to the collection
     * 
     * @param Config $config
     */
    public function addConfig(Config $config)
    {
        $this->configs[] = $config;
    }
    
    /**
     * Get configs
     * 
     * @return ArrayCollection
     */
    public function getConfigs()
    {
        return $this->configs;
    }
    
    /**
     * Set configs
     * @param array $configs
     */
    public function setConfigs(array $configs)
    {
        $this->configs = $configs;
    }
    
    public function removeConfig(Config $config)
    {
    	$key = array_search($config, $this->configs, true);
    	
    	if ($key !== false) {
    		unset($this->configs[$key]);
    	
    		return true;
    	}
    	
    	return false;
    }
}