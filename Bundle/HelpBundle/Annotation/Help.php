<?php

namespace Oro\Bundle\HelpBundle\Annotation;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\ConfigurationAnnotation;

/**
 * @Annotation
 */
class Help extends ConfigurationAnnotation
{
    const ALIAS = 'oro_help';

    /**
     * @var string
     */
    protected $controllerAlias;

    /**
     * @var string
     */
    protected $vendorAlias;

    /**
     * @var string
     */
    protected $actionAlias;

    /**
     * @var string
     */
    protected $bundleAlias;

    /**
     * @var string
     */
    protected $link;

    /**
     * @var string
     */
    protected $prefix;

    /**
     * @var string
     */
    protected $server;

    /**
     * @var string
     */
    protected $uri;

    /**
     * @return array
     */
    public function getConfigurationArray()
    {
        $optionsMap = array(
            'vendorAlias' => 'vendor',
            'bundleAlias' => 'bundle',
            'controllerAlias' => 'controller',
            'actionAlias' => 'action',
            'link' => 'link',
            'prefix' => 'prefix',
            'server' => 'server',
            'uri' => 'uri'
        );

        $configuration = array();
        foreach ($optionsMap as $property => $key) {
            if (isset($this->$property)) {
                $configuration[$key] = $this->$property;
            }
        }
        return $configuration;
    }

    /**
     * {@inheritDoc}
     */
    public function allowArray()
    {
        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function getAliasName()
    {
        return static::ALIAS;
    }

    /**
     * @return string
     */
    public function getLink()
    {
        return $this->link;
    }

    /**
     * @param string $link
     */
    public function setLink($link)
    {
        $this->link = $link;
    }

    /**
     * @return string
     */
    public function getPrefix()
    {
        return $this->prefix;
    }

    /**
     * @param string $prefix
     */
    public function setPrefix($prefix)
    {
        $this->prefix = $prefix;
    }

    /**
     * @return string
     */
    public function getServer()
    {
        return $this->server;
    }

    /**
     * @param string $server
     */
    public function setServer($server)
    {
        $this->server = $server;
    }

    /**
     * @return string
     */
    public function getUri()
    {
        return $this->uri;
    }

    /**
     * @param string $uri
     */
    public function setUri($uri)
    {
        $this->uri = $uri;
    }

    /**
     * @return string
     */
    public function getControllerAlias()
    {
        return $this->controllerAlias;
    }

    /**
     * @param string $controllerAlias
     */
    public function setControllerAlias($controllerAlias)
    {
        $this->controllerAlias = $controllerAlias;
    }

    /**
     * @return string
     */
    public function getActionAlias()
    {
        return $this->actionAlias;
    }

    /**
     * @param string $actionAlias
     */
    public function setActionAlias($actionAlias)
    {
        $this->actionAlias = $actionAlias;
    }

    /**
     * @return string
     */
    public function getBundleAlias()
    {
        return $this->bundleAlias;
    }

    /**
     * @param string $bundleAlias
     */
    public function setBundleAlias($bundleAlias)
    {
        $this->bundleAlias = $bundleAlias;
    }

    /**
     * @return string
     */
    public function getVendorAlias()
    {
        return $this->vendorAlias;
    }

    /**
     * @param string $vendorAlias
     */
    public function setVendorAlias($vendorAlias)
    {
        $this->vendorAlias = $vendorAlias;
    }
}
