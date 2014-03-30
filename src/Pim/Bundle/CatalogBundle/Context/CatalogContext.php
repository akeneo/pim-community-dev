<?php

namespace Pim\Bundle\CatalogBundle\Context;

/**
 * Allows to configure the context to use in queries you can execute and to ensure that the expecte
 * configuration is provided
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class CatalogContext
{
    /** @var array configuration */
    protected $configuration = array();

    /**
     * @return string
     */
    public function getLocaleCode()
    {
        return $this->getConfiguration('localeCode');
    }

    /**
     * @return string
     */
    public function getScopeCode()
    {
        return $this->getConfiguration('scopeCode');
    }

    /**
     * @param string $code
     *
     * @return catalogcontext
     */
    public function setLocaleCode($code)
    {
        return $this->setConfiguration('localeCode', $code);
    }

    /**
     * @param string $code
     *
     * @return catalogcontext
     */
    public function setScopeCode($code)
    {
        return $this->setConfiguration('scopeCode', $code);
    }

    /**
     * @return boolean
     */
    public function hasLocaleCode()
    {
        return (isset($this->configuration['localeCode']) && $this->configuration['localeCode'] !== null);
    }

    /**
     * @return boolean
     */
    public function hasScopeCode()
    {
        return (isset($this->configuration['scopeCode']) && $this->configuration['scopeCode'] !== null);
    }

    /**
     * @param string $key  the key
     * @param mixed  $data the data
     *
     * @return CatalogContext
     */
    public function setConfiguration($key, $data)
    {
        $this->configuration[$key]= $data;

        return $this;
    }

    /**
     * Get configuration
     *
     * @param string $key
     *
     * @return mixed
     *
     * @throws \LogicException
     */
    public function getConfiguration($key)
    {
        if (!isset($this->configuration[$key]) || $this->configuration[$key] === null) {
            throw new \LogicException(sprintf('"%s" expects to be configured with "%s"', get_class($this), $key));
        }

        return $this->configuration[$key];
    }
}
