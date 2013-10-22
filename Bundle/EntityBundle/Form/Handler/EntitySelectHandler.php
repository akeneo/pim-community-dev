<?php

namespace Oro\Bundle\EntityBundle\Form\Handler;

use Doctrine\ORM\QueryBuilder;

use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpFoundation\Request;

use Oro\Bundle\EntityBundle\Form\Type\EntitySelectType;
use Oro\Bundle\EntityBundle\ORM\OroEntityManager;

use Oro\Bundle\EntityExtendBundle\Extend\ExtendManager;
use Oro\Bundle\EntityExtendBundle\Tools\ExtendConfigDumper;

use Oro\Bundle\FormBundle\Autocomplete\SearchHandlerInterface;

use Oro\Bundle\SearchBundle\Engine\Indexer;

class EntitySelectHandler implements SearchHandlerInterface
{
    /**
     * @var OroEntityManager
     */
    protected $entityManager;

    /**
     * @var string
     */
    protected $entityName;

    /**
     * @var string
     */
    protected $fieldName;

    /**
     * @var array
     */
    protected $properties = array('id', 'text');

    /**
     * @var bool
     */
    private $hasMore;

    /**
     * @var bool
     */
    protected $isCustomField = false;

    /**
     * @param OroEntityManager $entityManager
     */
    public function __construct(OroEntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * {@inheritdoc}
     */
    public function convertItem($item)
    {
        $result = array();

        if ($this->entityName && $this->fieldName) {
            if ($this->isCustomField) {
                $result[$this->fieldName] = $this->getPropertyValue(
                    ExtendConfigDumper::FIELD_PREFIX . $this->fieldName,
                    $item
                );
            } else {
                $result[$this->fieldName] = $this->getPropertyValue($this->fieldName, $item);
            }

            foreach ($this->properties as $property) {
                $result[$property] = $this->getPropertyValue($property, $item);
            }
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function search($query, $page, $perPage)
    {
        list($query, $targetEntity, $targetField) = explode(',', $query);

        $this->entityName = str_replace('_', '\\', $targetEntity);

        $fieldConfig = $this->entityManager->getExtendManager()->getConfigProvider()
            ->getConfig($this->entityName, $targetField);

        if ($fieldConfig->is('owner', ExtendManager::OWNER_CUSTOM)) {
            $this->isCustomField = true;
        }

        $this->fieldName  = $targetField;

        return $this->formatResult($this->searchEntities($query, $targetField));
    }

    /**
     * {@inheritdoc}
     */
    public function getProperties()
    {
        return $this->properties;
    }

    /**
     * {@inheritdoc}
     */
    public function getEntityName()
    {
        return $this->entityName;
    }

    /**
     * Search and return entities
     *
     * @param $search
     * @param $targetField
     * @return array
     */
    protected function searchEntities($search, $targetField)
    {
        /** @var QueryBuilder $queryBuilder */
        $queryBuilder = $this->entityManager->getRepository($this->entityName)->createQueryBuilder('e');

        if ($this->isCustomField) {
            $targetField = ExtendConfigDumper::FIELD_PREFIX . $targetField;
        }

        $queryBuilder->where(
            $queryBuilder->expr()->like(
                'e.' . $targetField,
                $queryBuilder->expr()->literal($search . '%')
            )
        );

        return $queryBuilder->getQuery()->getArrayResult();
    }

    /**
     * @param array $items
     * @return array
     */
    protected function formatResult(array $items)
    {
        return array(
            'results' => $this->convertItems($items),
            'more'    => $this->hasMore
        );
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

        return $result;
    }
}
