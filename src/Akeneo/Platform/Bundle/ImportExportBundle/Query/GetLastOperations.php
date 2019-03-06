<?php
declare(strict_types=1);

namespace Akeneo\Platform\Bundle\ImportExportBundle\Query;

use Akeneo\Platform\Bundle\ImportExportBundle\Registry\NotVisibleJobsRegistry;
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
    /** @var Connection */
    private $connection;

    /** @var NotVisibleJobsRegistry */
    private $notVisibleJobs;

    public function __construct(Connection $connection, NotVisibleJobsRegistry $notVisibleJobs)
    {
        $this->connection = $connection;
        $this->notVisibleJobs = $notVisibleJobs;
    }

    /**
     * {@inheritdoc}
     */
    public function execute(UserInterface $user): array
    {
        $statement = $this->getQueryBuilder($user)->execute();

        return $statement->fetchAll();
    }

    /**
     * {@inheritdoc}
     */
    public function getQueryBuilder(UserInterface $user): QueryBuilder
    {
        $qb = $this->connection->createQueryBuilder();
        $qb
            ->select([
                'execution.id',
                'execution.start_time as date',
                'instance.id as job_instance_id',
                'instance.type',
                'instance.label',
                'execution.status',
                'COUNT(warning.id) as warningCount',
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
            ->leftJoin(
                'step',
                'akeneo_batch_warning',
                'warning',
                $qb->expr()->eq('warning.step_execution_id', 'step.id')
            )
            ->where($qb->expr()->eq('execution.user', ':user'))
            ->groupBy('execution.id')
            ->orderBy('execution.start_time', 'DESC')
            ->setMaxResults(10);

        $parameters = ['user' => $user->getUsername()];
        $types = ['user' => \PDO::PARAM_STR];
        if (!empty($this->notVisibleJobs->getCodes())) {
            $qb->andWhere($qb->expr()->notIn('instance.code', ':blackList'));

            $parameters['blackList'] = $this->notVisibleJobs->getCodes();
            $types['blackList'] = Connection::PARAM_STR_ARRAY;
        }

        $qb->setParameters($parameters, $types);

        return $qb;
    }
}
