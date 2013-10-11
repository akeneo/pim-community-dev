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
    protected $alias;

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
     * {@inheritDoc}
     */
    public function allowArray()
    {
        return false;
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
    public function getAlias()
    {
        return $this->alias;
    }

    /**
     * @param string $alias
     */
    public function setAlias($alias)
    {
        $this->alias = $alias;
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
}
