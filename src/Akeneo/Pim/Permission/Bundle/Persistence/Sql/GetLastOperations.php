<?php
declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Permission\Bundle\Persistence\Sql;

use Akeneo\Platform\Bundle\ImportExportBundle\Query\GetLastOperationsInterface;
use Akeneo\UserManagement\Component\Model\UserInterface;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;

/**
 * @author Willy Mesnage <willy.mesnage@akeneo.com>
 */
class GetLastOperations implements GetLastOperationsInterface
{
    /** @var GetLastOperationsInterface */
    private $lastOperations;

    public function __construct(GetLastOperationsInterface $lastOperations)
    {
        $this->lastOperations = $lastOperations;
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
        $qb = $this->lastOperations->getQueryBuilder($user);

        $qb
            ->innerJoin(
                'instance',
                'pimee_security_job_profile_access',
                'access',
                $qb->expr()->eq('instance.id', 'access.job_profile_id')
            )
            ->andWhere($qb->expr()->in('access.user_group_id', ':groups'))
            ->andWhere($qb->expr()->eq('access.execute_job_profile', true))
            ->setParameter('groups', $user->getGroupsIds(), Connection::PARAM_STR_ARRAY);

        return $qb;
    }
}
