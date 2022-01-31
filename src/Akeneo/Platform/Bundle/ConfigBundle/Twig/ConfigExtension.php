<?php

namespace Akeneo\Platform\Bundle\ConfigBundle\Twig;

use Akeneo\Platform\Bundle\ConfigBundle\Config\ConfigManager;
use Twig\TwigFunction;

class ConfigExtension extends \Twig\Extension\AbstractExtension
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
            new TwigFunction('oro_config_value', [$this, 'getUserValue']),
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
