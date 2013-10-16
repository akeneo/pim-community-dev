<?php

namespace Oro\Bundle\EntityConfigBundle\Provider;

use Doctrine\Common\Collections\ArrayCollection;

class ConfigProviderBag
{
    /**
     * @var ArrayCollection
     */
    protected $providers;

    public function __construct()
    {
        $this->providers = new ArrayCollection();
    }

    /**
     * @return ConfigProvider[]|ArrayCollection
     */
    public function getProviders()
    {
        return $this->providers;
    }

    /**
     * @param ConfigProvider $provider
     * @return $this
     */
    public function addProvider(ConfigProvider $provider)
    {
        $this->providers->set($provider->getScope(), $provider);

        return $this;
    }

    /**
     * @param $scope
     * @return ConfigProvider
     */
    public function getProvider($scope)
    {
        return $this->providers->get($scope);
    }

    /**
     * @param $scope
     * @return bool
     */
    public function hasProvider($scope)
    {
        return $this->providers->containsKey($scope);
    }
}
