<?php

namespace Oro\Bundle\FormBundle\EntityAutocomplete;

use Symfony\Component\DependencyInjection\ContainerInterface;

class ServiceSearchFactory implements SearchFactoryInterface
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * {@inheritdoc}
     */
    public function create(array $options)
    {
        if (!isset($options['options']['service'])) {
            throw new \RuntimeException('Option "options.service" is required');
        }

        $searchHandler = $this->container->get($options['options']['service']);
        if (!$searchHandler instanceof SearchHandlerInterface) {
            throw new \RuntimeException(
                sprintf(
                    'Service "%s" must be an instance of %s\\SearchHandlerInterface',
                    __NAMESPACE__,
                    $options['options']['service']
                )
            );
        }

        return $searchHandler;
    }
}
