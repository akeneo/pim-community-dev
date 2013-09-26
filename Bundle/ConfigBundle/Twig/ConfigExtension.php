<?php

namespace Oro\Bundle\ConfigBundle\Twig;

use Oro\Bundle\ConfigBundle\Config\ConfigManager;

class ConfigExtension extends \Twig_Extension
{
    /**
     * @var ConfigManager
     */
    protected $userConfigManager;

    /**
     * @var array
     */
    protected $entityOutput;

    public function __construct(ConfigManager $userConfigManager, $entityOutput = array())
    {
        $this->userConfigManager = $userConfigManager;
        $this->entityOutput      = $entityOutput;
    }

    /**
     * Returns a list of functions to add to the existing list.
     *
     * @return array An array of functions
     */
    public function getFunctions()
    {
        return array(
            'oro_config_value'  => new \Twig_Function_Method($this, 'getUserValue'),
            'oro_config_entity' => new \Twig_Function_Method($this, 'getEntityOutput'),
        );
    }

    /**
     * @param  string $name Setting name in "{bundle}.{setting}" format
     *
     * @return mixed
     */
    public function getUserValue($name)
    {
        return $this->userConfigManager->get($name);
    }

    /**
     * Get entity output config (if any provided).
     * Provided parameters:
     *  "icon_class"  - CSS class name for icon element
     *  "name"        - custom entity name
     *  "description" - entity description
     *
     * @param  string $class FQCN of the entity
     *
     * @return array
     */
    public function getEntityOutput($class)
    {
        $default = explode('\\', $class);

        return isset($this->entityOutput[$class])
            ? $this->entityOutput[$class]
            : array(
                'icon_class'  => '',
                'name'        => end($default),
                'description' => 'No description'
            );
    }

    /**
     * Returns the name of the extension.
     *
     * @return string
     */
    public function getName()
    {
        return 'config_extension';
    }
}
