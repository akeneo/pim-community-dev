<?php

declare(strict_types=1);

namespace Akeneo\Platform\Bundle\ImportExportBundle\Query;

use Akeneo\UserManagement\Component\Model\UserInterface;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;

/**
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GetLastOperations implements GetLastOperationsInterface
{
    private Connection $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * {@inheritdoc}
     */
    public function execute(?UserInterface $user = null): array
    {
        $statement = $this->getQueryBuilder($user)->execute();

        return $statement->fetchAllAssociative();
    }

    /**
     * {@inheritdoc}
     */
    public function getQueryBuilder(?UserInterface $user = null): QueryBuilder
    {
        $qb = $this->connection->createQueryBuilder();
        $qb
            ->select([
                'execution.id',
                'execution.start_time as date',
                'execution.user as username',
                'instance.id as job_instance_id',
                'instance.type',
                'instance.label',
                'execution.status',
                'SUM(step.warning_count) as warningCount',
            ])
            ->from('akeneo_batch_job_execution', 'execution')
            ->innerJoin(
                'execution',
                'akeneo_batch_job_instance',
                'instance',
                $qb->expr()->eq('instance.id', 'execution.job_instance_id')
            )
            ->leftJoin(
                'execution',
                'akeneo_batch_step_execution',
                'step',
                $qb->expr()->eq('step.job_execution_id', 'execution.id')
            )
            ->where($qb->expr()->eq('execution.is_visible', 1))
            ->groupBy('execution.id')
            ->orderBy('execution.start_time', 'DESC')
            ->setMaxResults(10);

        $parameters = [];
        $types = [];
        if (null !== $user) {
            $qb->andWhere($qb->expr()->eq('execution.user', ':user'));

            $parameters['user'] = $user->getUserIdentifier();
            $types['user'] = \PDO::PARAM_STR;
        }

        $qb->setParameters($parameters, $types);

        return $qb;
    }
}
