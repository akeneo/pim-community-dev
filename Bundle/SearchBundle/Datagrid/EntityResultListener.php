<?php

namespace Oro\Bundle\SearchBundle\Datagrid;

use Symfony\Component\Routing\Router;

use Oro\Bundle\SearchBundle\Engine\ObjectMapper;
use Oro\Bundle\SearchBundle\Query\Result\Item;
use Oro\Bundle\SearchBundle\Formatter\ResultFormatter;
use Oro\Bundle\GridBundle\EventDispatcher\ResultDatagridEvent;

class EntityResultListener
{
    /**
     * @var string
     */
    protected $datagridName;

    /**
     * @var ResultFormatter
     */
    protected $resultFormatter;

    /**
     * @var Router
     */
    protected $router;

    /**
     * @var ObjectMapper
     */
    protected $mapper;

    /**
     * @param ResultFormatter $resultFormatter
     * @param string $datagridName
     * @param ObjectMapper $mapper
     * @param Router $router
     */
    public function __construct(ResultFormatter $resultFormatter, $datagridName, ObjectMapper $mapper, Router $router)
    {
        $this->resultFormatter = $resultFormatter;
        $this->datagridName    = $datagridName;
        $this->mapper          = $mapper;
        $this->router          = $router;
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
     * Get entity string
     *
     * @param object $entity
     *
     * @return string
     */
    public function getEntityTitle($entity)
    {
        if ($this->mapper->getEntityMapParameter(get_class($entity), 'title_fields')) {
            $fields = $this->mapper->getEntityMapParameter(get_class($entity), 'title_fields');
            $title = array();
            foreach ($fields as $field) {
                $title[] = $this->mapper->getFieldValue($entity, $field);
            }
        } else {
            $title = array((string) $entity);
        }

        return implode(' ', $title);
    }

    /**
     * @param ResultDatagridEvent $event
     */
    public function processResult(ResultDatagridEvent $event)
    {
        if (!$event->isDatagridName($this->datagridName)) {
            return;
        }

        /** @var $rows Item[] */
        $rows = $event->getRows();
        $entities = $this->resultFormatter->getResultEntities($rows);

        // add entities to result rows
        $resultRows = array();
        foreach ($rows as $row) {
            $entity     = null;
            $entityName = $row->getEntityName();
            $entityId   = $row->getRecordId();
            if (isset($entities[$entityName][$entityId])) {
                $entity = $entities[$entityName][$entityId];
            }

            if (!$row->getRecordUrl()) {
                $row->setRecordUrl($this->getEntityUrl($entity));
            }

            if (!$row->getRecordTitle()) {
                $row->setRecordTitle($this->getEntityTitle($entity));
            }
            $resultRows[] = array(
                'indexer_item' => $row,
                'entity' => $entity,
            );
        }

        $event->setRows($resultRows);
    }
}
