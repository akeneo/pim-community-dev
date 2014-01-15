<?php

namespace Pim\Bundle\DataGridBundle\Extension\MassAction\Actions\Export;

use Symfony\Component\Serializer\SerializerInterface;
use Doctrine\ORM\EntityManager;
use Oro\Bundle\DataGridBundle\Extension\MassAction\MassActionHandlerInterface;
use Oro\Bundle\DataGridBundle\Extension\MassAction\MassActionMediatorInterface;
use Oro\Bundle\DataGridBundle\Datasource\Orm\ConstantPagerIterableResult;
use Oro\Bundle\DataGridBundle\Datasource\Orm\IterableResultInterface;

/**
 * Export action handler
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ExportMassActionHandler implements MassActionHandlerInterface
{
    /**
     * @staticvar int
     */
    const BATCH_SIZE = 2500;

    /**
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * @var SerializerInterface
     */
    protected $serializer;

    /**
     * @param EntityManager       $entityManager
     * @param SerializerInterface $serializer
     */
    public function __construct(EntityManager $entityManager, SerializerInterface $serializer)
    {
        $this->entityManager = $entityManager;
        $this->serializer    = $serializer;
    }

    /**
     * {@inheritDoc}
     */
    public function handle(MassActionMediatorInterface $mediator)
    {
        $results = $mediator->getResults();

        $qb = $mediator->getDatagrid()->getAcceptedDatasource()->getQueryBuilder();

        // $qb->orWhere($qb->getRootAlias().'.id NOT IN (:entityIds)');
        $qb->setFirstResult(null);
        $qb->setMaxResults(null);

        $results = $qb->getQuery()->execute();

        $data = array();
        foreach ($results as $result) {
            $entity = $result[0];
            $data[] = $this->serializer->serialize($entity, 'csv');
        }

        return $data;
    }

    /**
     * @param IterableResultInterface $result
     *
     * @return ConstantPagerIterableResult
     */
    protected function prepareIterableResult(IterableResultInterface $result)
    {
        $results =  new ConstantPagerIterableResult($result->getSource());
        $params = [];
        foreach ($result->getSource()->getParameters() as $param) {
            $params[$param->getName()] = $param->getValue();
        }
        $results->setParameters($params);

        return $results;
    }
}
