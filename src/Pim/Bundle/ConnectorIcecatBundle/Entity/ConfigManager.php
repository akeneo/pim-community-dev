<?php
namespace Pim\Bundle\ConnectorIcecatBundle\Entity;

use Pim\Bundle\ConnectorIcecatBundle\Entity\Config;

use Doctrine\Common\Persistence\ObjectManager;
use \Exception;
/**
 * Service class to get bundle configuration
 * An object manager must be set before use static methods
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class ConfigManager
{
    /**
     * Associative array to get Config entities (code => Config)
     * @staticvar
     * @var array
     */
    protected static $configs = array();

    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * @param ObjectManager $om
     */
    public function __construct($om)
    {
        $this->objectManager = $om;
    }

    /**
     * Get configuration from code
     *
     * @param string $code
     * 
     * @throws Exception
     * 
     * @return Config
     */
    public function get($code)
    {
        $config = self::getConfig();
        if (!isset($config[$code])) {
            throw new Exception('nonexistent config code');
        } else {
            return $config[$code];
        }
    }

    /**
     * Get configuration value from code
     *
     * @param  string    $code
     * 
     * @throws Exception
     * 
     * @return string
     */
    public function getValue($code)
    {
        return $this->get($code)->getValue();
    }

    /**
     * Get config associative array
     *
     * @throws Exception
     * @return array
     */
    public function getConfig()
    {
        if (!self::$configs) {
            $configs =     $this->findAll();
            // formating to an associative array (code => Config)
            foreach ($configs as $config) {
                self::$configs[$config->getCode()] = $config;
            }
        }

        return self::$configs;
    }

    /**
     * Find all Config objects
     *
     * @throws Exception
     * @return array
     */
    protected function findAll()
    {
        return $this->objectManager->getRepository('PimConnectorIcecatBundle:Config')->findAll();
    }
}
