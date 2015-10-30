<?php

namespace Oro\Bundle\NavigationBundle\Title;

use JMS\Serializer\Annotation\Type;

/**
 * Class StoredTitle
 * Used for json desirialization
 * @package Oro\Bundle\NavigationBundle\Title
 */
class StoredTitle
{
    /**
     * @Type("string")
     * @var string
     */
    private $template;

    /**
     * @Type("string")
     * @var string
     */
    private $shortTemplate;

    /**
     * @Type("array")
     * @var array
     */
    private $params = array();

    /**
     * @Type("string")
     * @var string
     */
    private $prefix = '';

    /**
     * @Type("string")
     * @var string
     */
    private $suffix = '';

    /**
     * Setter for template
     *
     * @param string $template
     * @return $this
     */
    public function setTemplate($template)
    {
        $this->template = $template;

        return $this;
    }

    /**
     * Getter for template
     *
     * @return string
     */
    public function getTemplate()
    {
        return $this->template;
    }

    /**
     * Set short template string
     *
     * @param string $shortTemplate
     * @return $this
     */
    public function setShortTemplate($shortTemplate)
    {
        $this->shortTemplate = $shortTemplate;

        return $this;
    }

    /**
     * Get short template string
     *
     * @return string
     */
    public function getShortTemplate()
    {
        return $this->shortTemplate;
    }

    /**
     * Setter for params
     *
     * @param array $params
     * @return $this
     */
    public function setParams($params)
    {
        $this->params = $params;

        return $this;
    }

    /**
     * Getter for params
     *
     * @return array
     */
    public function getParams()
    {
        return $this->params;
    }

    /**
     * Setter for prefix
     *
     * @param string $prefix
     * @return $this
     */
    public function setPrefix($prefix)
    {
        $this->prefix = $prefix;

        return $this;
    }

    /**
     * Getter for prefix
     *
     * @return string
     */
    public function getPrefix()
    {
        return $this->prefix;
    }

    /**
     * Setter for suffix
     *
     * @param string $suffix
     * @return $this
     */
    public function setSuffix($suffix)
    {
        $this->suffix = $suffix;

        return $this;
    }

    /**
     * Getter for suffix
     *
     * @return string
     */
    public function getSuffix()
    {
        return $this->suffix;
    }
}
