<?php

namespace Oro\Bundle\ConfigBundle\Twig;

use Oro\Bundle\ConfigBundle\Config\ConfigManager;

class ConfigExtension extends \Twig_Extension
{
    /**
     * @var ConfigManager
     */
    protected $userConfigManager;

    public function __construct(ConfigManager $userConfigManager)
    {
        $this->userConfigManager = $userConfigManager;
    }

    /**
     * Returns a list of functions to add to the existing list.
     *
     * @return array An array of functions
     */
    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction('oro_config_value', [$this, 'getUserValue']),
        ];
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
}
