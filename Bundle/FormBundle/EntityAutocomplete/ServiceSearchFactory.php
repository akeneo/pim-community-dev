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
    public function create(array $config)
    {
        if (!isset($config['service'])) {
            throw new \RuntimeException('Config option "service" is required');
        }

        $searchHandler = $this->container->get($config['service']);
        if (!$searchHandler instanceof SearchHandlerInterface) {
            throw new \RuntimeException(
                sprintf(
                    'Service "%s" must be an instance of %s\\SearchHandlerInterface',
                    __NAMESPACE__,
                    $config['service']
                )
            );
        }

        return $searchHandler;
    }
}
