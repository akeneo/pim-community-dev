<?php

namespace Oro\Bundle\TagBundle\Datagrid;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Query;


use Oro\Bundle\SearchBundle\Engine\ObjectMapper;
use Oro\Bundle\TagBundle\Entity\Tagging;
use Oro\Bundle\GridBundle\Datagrid\ORM\ProxyQuery;
use Oro\Bundle\SearchBundle\Query\Result\Item as ResultItem;
use Symfony\Component\Routing\Router;

class ResultsQuery extends ProxyQuery
{
    /**
     * @var ObjectMapper
     */
    protected $mapper;

    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @var Router
     */
    protected $router;

    /**
     * {@inheritdoc}
     */
    public function __construct($queryBuilder, ObjectMapper $mapper, EntityManager $em, Router $router)
    {
        $this->mapper = $mapper;
        $this->em = $em;
        $this->router = $router;

        return parent::__construct($queryBuilder);
    }

    /**
     * Get url for entity
     *
     * @param object $entity
     *
     * @return string
     */
    protected function getEntityUrl($entity)
    {
        if ($this->mapper->getEntityMapParameter(get_class($entity), 'route')) {
            $routeParameters = $this->mapper->getEntityMapParameter(get_class($entity), 'route');
            $routeData = array();
            if (isset($routeParameters['parameters']) && count($routeParameters['parameters'])) {
                foreach ($routeParameters['parameters'] as $parameter => $field) {
                    $routeData[$parameter] = $this->mapper->getFieldValue($entity, $field);
                }
            }

            return $this->router->generate(
                $routeParameters['name'],
                $routeData,
                true
            );
        }

        return '';
    }

    /**
     * {@inheritdoc}
     */
    public function execute(array $params = array(), $hydrationMode = null)
    {
        $originResults = parent::execute($params, $hydrationMode);

        $results = array();
        /** @var Tagging $item */
        foreach ($originResults as $item) {
            $results[] = new ResultItem(
                $this->em,
                $item->getEntityName(),
                $item->getRecordId(),
                null,
                $this->getEntityUrl(
                    $this->em->getRepository($item->getEntityName())->find($item->getRecordId())
                ),
                null,
                $this->mapper->getEntityConfig($item->getEntityName())
            );
        }

        return $results;
    }
}
