<?php

namespace Oro\Bundle\FormBundle\EntityAutocomplete\Doctrine;

use Doctrine\ORM\QueryBuilder;

use Symfony\Component\DependencyInjection\ContainerInterface;

use Oro\Bundle\FormBundle\EntityAutocomplete\Property;
use Oro\Bundle\FormBundle\EntityAutocomplete\SearchFactoryInterface;

class QueryBuilderSearchFactory implements SearchFactoryInterface
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
        if (!isset($options['properties'])) {
            throw new \RuntimeException('Option "properties" is required');
        }

        if (!isset($options['options']['query_builder_service'])) {
            throw new \RuntimeException('Option "options.query_builder_service" is required');
        }

        $queryBuilder = $this->container->get($options['options']['query_builder_service']);
        if (!$queryBuilder instanceof QueryBuilder) {
            throw new \RuntimeException(
                sprintf(
                    'Service "%s" must be an instance of Doctrine\\ORM\\QueryBuilder',
                    $options['options']['query_builder_service']
                )
            );
        }

        $queryEntityAlias = isset($options['options']['query_entity_alias'])
            ? $options['options']['query_entity_alias'] : null;

        return new QueryBuilderSearchHandler(
            $queryBuilder,
            $options['properties'],
            $queryEntityAlias
        );
    }
}
