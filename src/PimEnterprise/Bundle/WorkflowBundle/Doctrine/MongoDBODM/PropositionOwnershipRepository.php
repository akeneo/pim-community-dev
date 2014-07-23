<?php

namespace PimEnterprise\Bundle\WorkflowBundle\Doctrine\MongoDBODM;

use Symfony\Component\Security\Core\User\UserInterface;
use Doctrine\ORM\AbstractQuery;
use Oro\Bundle\UserBundle\Entity\Role;
use Oro\Bundle\UserBundle\Entity\User;
use PimEnterprise\Bundle\SecurityBundle\Entity\Repository\CategoryAccessRepository;
use PimEnterprise\Bundle\WorkflowBundle\Model\Proposition;
use PimEnterprise\Bundle\WorkflowBundle\Repository\PropositionOwnershipRepositoryInterface;
use PimEnterprise\Bundle\WorkflowBundle\Repository\PropositionRepositoryInterface;

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

    /** @var CategoryAccessRepository */
    protected $catAccessRepo;

    /**
     * @param PropositionRepositoryInterface $propositionRepo
     * @param CategoryAccessRepository       $catAccessRepo
     */
    public function __construct(
        PropositionRepositoryInterface $propositionRepo,
        CategoryAccessRepository $catAccessRepo
    ) {
        $this->propositionRepo = $propositionRepo;
        $this->catAccessRepo = $catAccessRepo;
    }

    /**
     * {@inheritdoc}
     */
    public function findApprovableByUser(UserInterface $user, $limit = null)
    {
        $qb = $this->propositionRepo->createQueryBuilder();
        $qb
            ->field('status')->equals(Proposition::READY)
            ->field('categoryIds')->in($this->getGrantedCategoryIds($user))
            ->sort('createdAt', 'desc');

        if (null !== $limit) {
            $qb->limit($limit);
        }

        return $qb->getQuery()->execute();
    }

    /**
     * Get ids of categories the given user has ownership rights to
     *
     * @param User $user
     *
     * @return integer[]
     */
    protected function getGrantedCategoryIds(UserInterface $user)
    {
        $qb = $this->catAccessRepo->createQueryBuilder('o');

        $qb
            ->join('o.category', 'category')
            ->select('category.id')
            ->where($qb->expr()->in('o.userGroup', ':userGroups'))
            ->andWhere('o.ownProducts = 1')
            ->setParameter('userGroups', $user->getGroups()->toArray());

        $result = $qb->getQuery()->getResult(AbstractQuery::HYDRATE_ARRAY);

        $grantedCategoryIds = [];
        foreach ($result as $row) {
            $grantedCategoryIds[] = $row['id'];
        }

        return $grantedCategoryIds;
    }
}
