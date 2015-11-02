<?php
namespace Oro\Bundle\NavigationBundle\Title\TitleReader;

use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

class ConfigReader extends Reader
{
    /**
     * @var array
     */
    private $configData = [];

    public function __construct(array $configData)
    {
        $this->configData = $configData;
    }

    /**
     * Get Route/Title information from bundle configs
     *
     * @param  array                                                                        $routes
     * @throws \Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     * @return array
     */
    public function getData(array $routes)
    {
        $data = [];

        foreach ($this->configData as $route => $title) {
            if (array_key_exists($route, $routes)) {
                $data[$route] = $title;
            } else {
                throw new InvalidConfigurationException(
                    sprintf('Title for route "%s" could not be saved. Route not found.', $route)
                );
            }
        }

        return $data;
    }
}
