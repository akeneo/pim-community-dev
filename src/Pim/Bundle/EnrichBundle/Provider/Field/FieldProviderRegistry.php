<?php

namespace Pim\Bundle\EnrichBundle\Provider\Field;

/**
 * Field provider registry
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FieldProviderRegistry implements FieldProviderInterface
{
    /** @var array */
    protected $providers = [];

    /**
     * {@inheritdoc}
     */
    public function getField($element)
    {
        foreach ($this->providers as $provider) {
            if ($provider->supports($element)) {
                return $provider->getField($element);
            }
        }

        throw new \RuntimeException('No compatible Field provider found.');
    }

    /**
     * {@inheritdoc}
     */
    public function supports($element)
    {
        foreach ($this->providers as $provider) {
            if ($provider->supports($element)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Add a provider to the registry
     *
     * @param FieldProviderInterface $provider
     */
    public function addProvider(FieldProviderInterface $provider)
    {
        $this->providers[] = $provider;
    }
}
