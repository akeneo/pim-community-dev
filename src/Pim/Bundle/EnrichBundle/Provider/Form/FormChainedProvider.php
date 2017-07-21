<?php

namespace Pim\Bundle\EnrichBundle\Provider\Form;

/**
 * Form chained provider
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FormChainedProvider implements FormProviderInterface
{
    /** @var FormProviderInterface[] */
    protected $providers = [];

    /**
     * {@inheritdoc}
     */
    public function getForm($element)
    {
        foreach ($this->providers as $provider) {
            if ($provider->supports($element)) {
                return $provider->getForm($element);
            }
        }

        throw new NoCompatibleFormProviderFoundException();
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
     * @param FormProviderInterface $provider
     */
    public function addProvider(FormProviderInterface $provider)
    {
        $this->providers[] = $provider;
    }
}
