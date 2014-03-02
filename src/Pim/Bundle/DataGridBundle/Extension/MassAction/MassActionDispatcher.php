<?php

namespace Pim\Bundle\DataGridBundle\Extension\MassAction;

use Doctrine\ORM\QueryBuilder;
use Oro\Bundle\DataGridBundle\Datagrid\DatagridInterface;
use Oro\Bundle\DataGridBundle\Extension\MassAction\MassActionMediator;
use Oro\Bundle\DataGridBundle\Extension\MassAction\MassActionDispatcher as OroMassActionDispatcher;
use Oro\Bundle\FilterBundle\Grid\Extension\OrmFilterExtension;

/**
 * Overriden MassActionDispatcher to remove flexible pagination
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MassActionDispatcher extends OroMassActionDispatcher
{
    /**
     * {@inheritdoc}
     */
    public function dispatch($datagridName, $actionName, array $parameters, array $data = [])
    {
        $inset   = isset($parameters['inset'])   ? $parameters['inset']   : true;
        $values  = isset($parameters['values'])  ? $parameters['values']  : [];
        $filters = isset($parameters['filters']) ? $parameters['filters'] : [];

        if ($inset && empty($values)) {
            throw new \LogicException(sprintf('There is nothing to do in mass action "%s"', $actionName));
        }

        // create datagrid
        $datagrid = $this->manager->getDatagrid($datagridName);

        // set filter data
        $this->requestParams->set(OrmFilterExtension::FILTER_ROOT_PARAM, $filters);

        // create mediator
        $massAction     = $this->getMassActionByName($actionName, $datagrid);
        $identifier     = $this->getIdentifierField($massAction);
        $qb             = $this->prepareQueryBuilder($this->getDatagridQuery($datagrid, $identifier, $inset, $values));
        $resultIterator = $this->getResultIterator($qb);
        $mediator       = new MassActionMediator($massAction, $datagrid, $resultIterator, $data);

        // perform mass action
        $handle = $this->getMassActionHandler($massAction);
        $result = $handle->handle($mediator);

        return $result;
    }

    /**
     * Override to ensure a not indexed hydration
     *
     * {@inheritdoc}
     */
    protected function getDatagridQuery(DatagridInterface $datagrid, $idField = 'id', $inset = true, $values = [])
    {
        $datasource = $datagrid->getDatasource();

        /** @var QueryBuilder $qb */
        $qb = $datagrid->getAcceptedDatasource()->getQueryBuilder();
        if ($values) {
            $valueWhereCondition =
                $inset
                    ? $qb->expr()->in($idField, $values)
                    : $qb->expr()->notIn($idField, $values);
            $qb->andWhere($valueWhereCondition);
        }

        $rootAlias = $qb->getRootAlias();
        $from      = current($qb->getDQLPart('from'));
        $entity    = $from->getFrom();

        $qb->resetDQLPart('select');
        $qb->select($rootAlias);
        $qb->resetDQLPart('from');
        $qb->from($entity, $rootAlias);

        return $qb;
    }

    /**
     * Remove 'entityIds' part from querybuilder (added by flexible pager)
     *
     * @param QueryBuilder $qb
     *
     * @return QueryBuilder
     */
    protected function prepareQueryBuilder(QueryBuilder $qb)
    {
        $whereParts = $qb->getDQLPart('where')->getParts();
        $qb->resetDQLPart('where');

        foreach ($whereParts as $part) {
            if (!is_string($part) || !strpos($part, 'entityIds')) {
                $qb->andWhere($part);
            }
        }

        $qb->setParameters(
            $qb->getParameters()->filter(
                function ($parameter) {
                    return $parameter->getName() !== 'entityIds';
                }
            )
        );

        return $qb;
    }
}
