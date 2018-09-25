<?php

namespace Akeneo\Platform\Bundle\UIBundle\Provider\Field;

/**
 * Field chained provider
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FieldChainedProvider implements FieldProviderInterface
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
     * Add a provider
     *
     * @param FieldProviderInterface $provider
     */
    public function addProvider(FieldProviderInterface $provider)
    {
        $this->providers[] = $provider;
    }
}
