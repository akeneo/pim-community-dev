<?php

namespace Oro\Bundle\FormBundle\EntityAutocomplete\Flexible;

use Symfony\Component\DependencyInjection\ContainerInterface;

use Oro\Bundle\FlexibleEntityBundle\Manager\FlexibleManager;
use Oro\Bundle\FlexibleEntityBundle\Manager\FlexibleManagerRegistry;
use Oro\Bundle\FormBundle\EntityAutocomplete\SearchFactoryInterface;

class FlexibleSearchFactory implements SearchFactoryInterface
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var FlexibleManagerRegistry
     */
    protected $flexibleManagerRegistry;

    /**
     * @param ContainerInterface $container
     * @param FlexibleManagerRegistry $flexibleManagerRegistry
     */
    public function __construct(ContainerInterface $container, FlexibleManagerRegistry $flexibleManagerRegistry)
    {
        $this->container = $container;
        $this->flexibleManagerRegistry = $flexibleManagerRegistry;
    }

    /**
     * {@inheritdoc}
     */
    public function create(array $options)
    {
        if (isset($options['options']['flexible_manager'])) {
            $flexibleManager = $this->container->get($options['options']['flexible_manager']);
            if (!$flexibleManager instanceof FlexibleManager) {
                throw new \RuntimeException(
                    sprintf(
                        'Service "%s" must be an instance of '
                        . 'Oro\\Bundle\\FlexibleEntityBundle\\Manager\\FlexibleManager',
                        $options['options']['flexible_manager']
                    )
                );
            }
        } else {
            if (!isset($options['entity_class'])) {
                throw new \RuntimeException('Option "entity_class" is required');
            }
            $flexibleManager = $this->flexibleManagerRegistry->getManager($options['entity_class']);
        }

        if (!isset($options['properties'])) {
            throw new \RuntimeException('Option "properties" is required');
        }

        return new FlexibleSearchHandler(
            $flexibleManager->getFlexibleRepository(),
            $options['properties']
        );
    }
}
