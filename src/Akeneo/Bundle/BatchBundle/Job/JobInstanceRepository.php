<?php

namespace Akeneo\Bundle\BatchBundle\Job;

use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Doctrine\ORM\EntityRepository;

/**
 * Job instance repository
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class JobInstanceRepository extends EntityRepository implements IdentifiableObjectRepositoryInterface
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
    public function findOneByCode($code)
    {
        $qb = $this->createQueryBuilder('job_instance');
        $qb
            ->select('job_instance')
            ->where($qb->expr()->eq('job_instance.code', ':code'))
            ->setParameter('code', $code);

        return $qb->getQuery()->getArrayResult();
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentifierProperties()
    {
        return ['code'];
    }
}
