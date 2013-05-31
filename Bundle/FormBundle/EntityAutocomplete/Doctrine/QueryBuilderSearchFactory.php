<?php

namespace Oro\Bundle\FormBundle\EntityAutocomplete\Doctrine;

use Doctrine\ORM\QueryBuilder;

use Symfony\Component\DependencyInjection\ContainerInterface;

use Oro\Bundle\FormBundle\EntityAutocomplete\SearchFactoryInterface;
use Oro\Bundle\FormBundle\EntityAutocomplete\SearchPropertyConfig;

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
    public function create(array $config)
    {
        if (!isset($config['query_builder_service'])) {
            throw new \RuntimeException('Config option "query_builder_service" is required');
        }

        $queryBuilder = $this->container->get($config['query_builder_service']);
        if (!$queryBuilder instanceof QueryBuilder) {
            throw new \RuntimeException(
                sprintf(
                    'Service "%s" must be an instance of Doctrine\\ORM\\QueryBuilder',
                    $config['query_builder_service']
                )
            );
        }

        if (!isset($config['properties'])) {
            throw new \RuntimeException('Config option "properties" is required');
        }

        $queryEntityAlias = isset($config['query_entity_alias']) ? $config['query_entity_alias'] : null;

        return new QueryBuilderSearchHandler(
            $queryBuilder,
            $this->createSearchPropertiesConfig($config['properties']),
            $queryEntityAlias
        );
    }

    /**
     * @param array $config
     * @return SearchPropertyConfig[]
     */
    protected function createSearchPropertiesConfig(array $config)
    {
        $result = array();
        foreach ($config['properties'] as $propertyConfig) {
            $result[] = SearchPropertyConfig::create($propertyConfig);
        }
        return $result;
    }
}
