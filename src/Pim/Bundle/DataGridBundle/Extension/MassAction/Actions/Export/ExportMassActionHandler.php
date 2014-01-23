<?php

namespace Pim\Bundle\DataGridBundle\Extension\MassAction\Actions\Export;

use Symfony\Component\Serializer\SerializerInterface;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\QueryBuilder;
use Oro\Bundle\DataGridBundle\Extension\MassAction\MassActionHandlerInterface;
use Oro\Bundle\DataGridBundle\Extension\MassAction\MassActionMediatorInterface;

/**
 * Export action handler
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ExportMassActionHandler implements MassActionHandlerInterface
{
    /**
     * @staticvar int
     */
    const BATCH_SIZE = 250;

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
        $qb = $mediator->getDatagrid()->getAcceptedDatasource()->getQueryBuilder();
        $qb = $this->prepareQueryBuilder($qb);

        $results = $qb->getQuery()->execute();

        $context = [
            'withHeader'    => true,
            'heterogeneous' => true
        ];

        $entities = array_map(
            function ($result) {
                return $result[0];
            },
            $results
        );

        return $this->serializer->serialize($entities, 'csv', $context);
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

        $parameters = $qb->getParameters()->filter(
            function ($parameter) {
                return $parameter->getName() !== 'entityIds';
            }
        );

        $qb->setParameters($parameters);

        return $qb;
    }
}
