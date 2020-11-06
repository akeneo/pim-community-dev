<?php

namespace Akeneo\Pim\Enrichment\Bundle\Context;

/**
 * Allows to configure the context to use in queries you can execute and to ensure that
 * the expected configuration is provided
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class CatalogContext
{
    /** @var array configuration */
    protected $configuration = [];

    public function getLocaleCode(): string
    {
        return $this->getConfiguration('localeCode');
    }

    public function getScopeCode(): string
    {
        return $this->getConfiguration('scopeCode');
    }

    /**
     * @param string $code
     */
    public function setLocaleCode(string $code): \Akeneo\Pim\Enrichment\Bundle\Context\CatalogContext
    {
        return $this->setConfiguration('localeCode', $code);
    }

    /**
     * @param string $code
     */
    public function setScopeCode(string $code): \Akeneo\Pim\Enrichment\Bundle\Context\CatalogContext
    {
        return $this->setConfiguration('scopeCode', $code);
    }

    public function hasLocaleCode(): bool
    {
        return (isset($this->configuration['localeCode']) && $this->configuration['localeCode'] !== null);
    }

    public function hasScopeCode(): bool
    {
        return (isset($this->configuration['scopeCode']) && $this->configuration['scopeCode'] !== null);
    }

    /**
     * @param string $key  the key
     * @param mixed  $data the data
     */
    public function setConfiguration(string $key, $data): self
    {
        $this->configuration[$key] = $data;

        return $this;
    }

    /**
     * Get configuration
     *
     * @param string $key
     *
     * @throws \LogicException
     *
     * @return mixed
     */
    public function getConfiguration(string $key)
    {
        if (!isset($this->configuration[$key]) || $this->configuration[$key] === null) {
            throw new \LogicException(sprintf('"%s" expects to be configured with "%s"', get_class($this), $key));
        }

        return $this->configuration[$key];
    }
}
