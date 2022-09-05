<?php

namespace Akeneo\Tool\Bundle\BatchBundle\Job;

use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\RemovableObjectRepositoryInterface;
use Doctrine\ORM\EntityRepository;

/**
 * Job instance repository
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class JobInstanceRepository extends EntityRepository implements IdentifiableObjectRepositoryInterface, RemovableObjectRepositoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function findOneByIdentifier($code)
    {
        return $this->findOneBy(['code' => $code]);
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentifierProperties()
    {
        return ['code'];
    }

    public function remove(string $identifier): void
    {
        $sql = <<<SQL
    DELETE FROM akeneo_batch_job_instance
    WHERE code = :code
SQL;

        $this->getEntityManager()->getConnection()->executeQuery($sql, ['code' => $identifier]);
    }
}
