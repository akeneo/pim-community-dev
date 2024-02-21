<?php

namespace Akeneo\Platform\Bundle\UIBundle\Provider\EmptyValue;

/**
 * EmptyValue chained provider
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class EmptyValueChainedProvider implements EmptyValueProviderInterface
{
    /** @var array */
    protected $providers = [];

    /**
     * {@inheritdoc}
     */
    public function getEmptyValue($element)
    {
        foreach ($this->providers as $provider) {
            if ($provider->supports($element)) {
                return $provider->getEmptyValue($element);
            }
        }

        throw new \RuntimeException('No compatible EmptyValue provider found.');
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
     * Add a provider
     *
     * @param EmptyValueProviderInterface $provider
     */
    public function addProvider(EmptyValueProviderInterface $provider)
    {
        $this->providers[] = $provider;
    }
}
