<?php

namespace PimEnterprise\Bundle\WorkflowBundle\Doctrine\MongoDBODM;

use Doctrine\ORM\AbstractQuery;
use Oro\Bundle\UserBundle\Entity\User;
use PimEnterprise\Bundle\WorkflowBundle\Repository\PropositionOwnershipRepositoryInterface;
use PimEnterprise\Bundle\WorkflowBundle\Repository\PropositionRepositoryInterface;
use PimEnterprise\Bundle\SecurityBundle\Entity\Repository\CategoryAccessRepository;
use PimEnterprise\Bundle\WorkflowBundle\Model\Proposition;
use Oro\Bundle\UserBundle\Entity\Role;

/**
 * Proposition ownership repository for MongoDB
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class PropositionOwnershipRepository implements PropositionOwnershipRepositoryInterface
{
    /** @var PropositionRepositoryInterface */
    protected $propositionRepo;

    /**
     * @param PropositionRepositoryInterface $propositionRepo
     */
    public function __construct(
        PropositionRepositoryInterface $propositionRepo
    ) {
        $this->propositionRepo = $propositionRepo;
    }

    /**
     * {@inheritdoc}
     */
    public function findApprovableByUser(User $user, $limit = null)
    {
        $roles = array_map(
            function (Role $role) {
                return $role->getRole();
            },
            $user->getRoles()
        );

        $qb = $this->propositionRepo->createQueryBuilder();
        $qb
            ->field('status')->equals(Proposition::READY)
            ->field('reviewers')->in($roles)
            ->sort('createdAt', 'desc');

        if (null !== $limit) {
            $qb->limit($limit);
        }

        return $qb->getQuery()->execute();
    }
}
