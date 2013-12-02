<?php

namespace Oro\Bundle\FormBundle\Autocomplete;

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
    protected $entitySearchAlias;

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
     * @param string $entityName
     * @param array $properties
     */
    public function __construct($entityName, array $properties)
    {
        $this->entityName = $entityName;
        $this->properties = $properties;
    }

    /**
     * {@inheritdoc}
     */
    public function getProperties()
    {
        return $this->properties;
    }

    /**
     * @param Indexer $indexer
     * @param array $config
     * @throws \RuntimeException
     */
    public function initSearchIndexer(Indexer $indexer, array $config)
    {
        $this->indexer = $indexer;
        if (empty($config[$this->entityName]['alias'])) {
            throw new \RuntimeException("Cannot init entity search alias.");
        }
        $this->entitySearchAlias = $config[$this->entityName]['alias'];
    }

    /**
     * @param ManagerRegistry $managerRegistry
     * @throws \RuntimeException
     */
    public function initDoctrinePropertiesByManagerRegistry(ManagerRegistry $managerRegistry)
    {
        $objectManager = $managerRegistry->getManagerForClass($this->entityName);
        if (!$objectManager instanceof EntityManager) {
            throw new \RuntimeException(
                'Object manager for "%s" expected to be an instance of "%s".',
                $this->entityName,
                'Doctrine\ORM\EntityManager'
            );
        }
        $this->initDoctrinePropertiesByEntityManager($objectManager);
    }

    /**
     * @param EntityManager $entityManager
     */
    public function initDoctrinePropertiesByEntityManager(EntityManager $entityManager)
    {
        $this->entityRepository = $entityManager->getRepository($this->entityName);
        $this->idFieldName = $this->getEntityIdentifierFieldName($entityManager);
    }

    /**
     * @param EntityManager $entityManager
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
        $this->checkAllDependenciesInjected();

        $page = (int)$page > 0 ? (int)$page : 1;
        $perPage = (int)$perPage > 0 ? (int)$perPage : 10;
        $perPage += 1;

        $items = $this->searchEntities($query, ($page - 1) * $perPage, $perPage);

        $this->hasMore = count($items) == $perPage;
        if ($this->hasMore) {
            $items = array_slice($items, 0, $perPage - 1);
        }

        return $this->formatResult($items);
    }

    /**
     * @throws \RuntimeException
     */
    private function checkAllDependenciesInjected()
    {
        if (!$this->indexer || !$this->entitySearchAlias || !$this->entityRepository || !$this->idFieldName) {
            throw new \RuntimeException('Search handler is not fully configured');
        }
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

        $resultEntities = array();

        if ($entityIds) {
            /** @var QueryBuilder $queryBuilder */
            $queryBuilder = $this->entityRepository->createQueryBuilder('e');
            $queryBuilder->where($queryBuilder->expr()->in('e.' . $this->idFieldName, $entityIds));
            $currentEntities = $queryBuilder->getQuery()->getResult();

            foreach ($currentEntities as $entity) {
                $resultEntities[] = $entity;
            }
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
        $result = $this->indexer->simpleSearch($search, $firstResult, $maxResults, $this->entitySearchAlias);
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
     * {@inheritdoc}
     */
    public function convertItem($item)
    {
        $result = array();

        if ($this->idFieldName) {
            $result[$this->idFieldName] = $this->getPropertyValue($this->idFieldName, $item);
        }

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
