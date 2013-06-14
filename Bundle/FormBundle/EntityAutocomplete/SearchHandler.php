<?php

namespace Oro\Bundle\FormBundle\EntityAutocomplete;

use Doctrine\Common\Persistence\ManagerRegistry;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\QueryBuilder;

use Oro\Bundle\FlexibleEntityBundle\Model\FlexibleValueInterface;

use Oro\Bundle\SearchBundle\Engine\Indexer;

class SearchHandler implements SearchHandlerInterface
{
    /**
     * @var Indexer
     */
    protected $indexer;

    /**
     * @var EntityRepository
     */
    protected $entityRepository;

    /**
     * @var string
     */
    protected $entityName;

    /**
     * @var string
     */
    protected $idFieldName;

    /**
     * @var array
     */
    protected $properties;

    /**
     * @var boolean
     */
    private $hasMore;

    /**
     * @param Indexer $indexer
     * @param ManagerRegistry $managerRegistry
     * @param string $entityName
     * @param array $properties
     */
    public function __construct(
        Indexer $indexer,
        ManagerRegistry $managerRegistry,
        $entityName,
        $properties
    ) {
        $this->indexer = $indexer;
        $entityManager = $managerRegistry->getManager($entityName);
        $this->entityRepository = $entityManager->getRepository($entityName);
        $this->entityName = $entityName;
        $this->idFieldName = $this->getEntityIdentifierFieldName($entityManager);
        $this->properties = array($this->idFieldName);
        foreach ($properties as $property) {
            $this->properties[] = $property;
        }
    }

    /**
     * @return string
     */
    protected function getEntityIdentifierFieldName(EntityManager $entityManager)
    {
        /** @var $metadata \Doctrine\ORM\Mapping\ClassMetadata */
        $metadata = $entityManager->getMetadataFactory()->getMetadataFor($this->entityName);
        return $metadata->getSingleIdentifierFieldName();
    }

    /**
     * {@inheritdoc}
     */
    public function search($query, $page, $perPage)
    {
        $page = (int)$page > 0 ? (int)$page : 1;
        $perPage = (int)$perPage > 0 ? (int)$perPage : 1;
        $perPage += 1;

        $items = $this->searchEntities($query, ($page - 1) * $perPage, $perPage);

        $this->hasMore = count($items) == $perPage;
        if ($this->hasMore) {
            $items = array_slice($items, 0, $perPage - 1);
        }

        return $this->formatResult($items);
    }

    /**
     * @param array $items
     * @return array
     */
    protected function formatResult(array $items)
    {
        return array(
            'results' => $this->convertItems($items),
            'more' => $this->hasMore
        );
    }

    /**
     * Search and return entities
     *
     * @param string $search
     * @param int $firstResult
     * @param int $maxResults
     * @return array
     */
    protected function searchEntities($search, $firstResult, $maxResults)
    {
        $entityIds = $this->searchIds($search, $firstResult, $maxResults);

        /** @var QueryBuilder $queryBuilder */
        $queryBuilder = $this->entityRepository->createQueryBuilder('e');
        $queryBuilder->where($queryBuilder->expr()->in('e.' . $this->idFieldName, $entityIds));
        $currentEntities = $queryBuilder->getQuery()->getResult();

        $resultEntities = array();
        foreach ($currentEntities as $entity) {
            $resultEntities[] = $entity;
        }

        return $resultEntities;
    }

    /**
     * @param string $search
     * @param int $firstResult
     * @param int $maxResults
     * @return array
     */
    protected function searchIds($search, $firstResult, $maxResults)
    {
        $result = $this->indexer->simpleSearch($search, $firstResult, $maxResults, $this->entityName);
        $elements = $result->getElements();

        $ids = array();
        foreach ($elements as $element) {
            $ids[] = $element->getRecordId();
        }

        return $ids;
    }

    /**
     * @param array $items
     * @return array
     */
    protected function convertItems(array $items)
    {
        $result = array();
        foreach ($items as $item) {
            $result[] = $this->convertItem($item);
        }
        return $result;
    }

    /**
     * @param mixed $item
     * @return array
     */
    protected function convertItem($item)
    {
        $result = array();

        foreach ($this->properties as $property) {
            $result[$property] = $this->getPropertyValue($property, $item);
        }

        return $result;
    }

    /**
     * @param string $name
     * @param object|array $item
     * @return mixed
     */
    protected function getPropertyValue($name, $item)
    {
        $result = null;

        if (is_object($item)) {
            $method = 'get' . str_replace(' ', '', str_replace('_', ' ', ucwords($name)));
            if (method_exists($item, $method)) {
                $result = $item->$method();
            } elseif (isset($item->$name)) {
                $result = $item->$name;
            }
        } elseif (is_array($item) && array_key_exists($name, $item)) {
            $result = $item[$name];
        }

        if ($result instanceof FlexibleValueInterface) {
            $result = $result->getData();
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function getEntityName()
    {
        return $this->entityName;
    }
}
