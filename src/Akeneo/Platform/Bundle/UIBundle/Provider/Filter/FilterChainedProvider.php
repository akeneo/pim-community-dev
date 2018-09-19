<?php

namespace Akeneo\Platform\Bundle\UIBundle\Provider\Filter;

/**
 * Filter chained provider
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FilterChainedProvider implements FilterProviderInterface
{
    /** @var array */
    protected $providers = [];

    /**
     * {@inheritdoc}
     */
    public function getFilters($element)
    {
        $filters = [];
        foreach ($this->providers as $provider) {
            if ($provider->supports($element)) {
                $filters = array_merge($filters, $provider->getFilters($element));
            }
        }
        return $filters;
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
     * @param FilterProviderInterface $provider
     */
    public function addProvider(FilterProviderInterface $provider)
    {
        $this->providers[] = $provider;
    }
}
