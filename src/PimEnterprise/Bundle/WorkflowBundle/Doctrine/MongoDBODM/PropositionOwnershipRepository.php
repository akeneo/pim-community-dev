<?php

namespace PimEnterprise\Bundle\WorkflowBundle\Doctrine\MongoDBODM;

use Doctrine\ORM\AbstractQuery;
use Oro\Bundle\UserBundle\Entity\User;
use PimEnterprise\Bundle\WorkflowBundle\Repository\PropositionOwnershipRepositoryInterface;
use PimEnterprise\Bundle\WorkflowBundle\Repository\PropositionRepositoryInterface;
use PimEnterprise\Bundle\SecurityBundle\Entity\Repository\CategoryOwnershipRepository;
use PimEnterprise\Bundle\WorkflowBundle\Model\Proposition;

/**
 * Proposition ownership repository for MongoDB
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class PropositionOwnershipRepository implements PropositionOwnershipRepositoryInterface
{
    /**
     * @var PropositionRepositoryInterface
     */
    protected $propositionRepo;

    /**
     * @var CategoryOwnershipRepository
     */
    protected $catOwnershipRepo;

    /**
     * @param PropositionRepositoryInterface $propositionRepo
     * @param CategoryOwnershipRepository    $catOwnershipRepo
     */
    public function __construct(
        PropositionRepositoryInterface $propositionRepo,
        CategoryOwnershipRepository $catOwnershipRepo
    ) {
        $this->propositionRepo  = $propositionRepo;
        $this->catOwnershipRepo = $catOwnershipRepo;
    }

    /**
     * {@inheritdoc}
     */
    public function findApprovableByUser(User $user, $limit = null)
    {
        $qb = $this->propositionRepo->createQueryBuilder();

        $qb
            ->field('status')->equals(Proposition::READY)
            // TODO: Return only propositions with products in these categories
            // ->field('product.categoryIds')->in($this->getGrantedCategoryIds($user))
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
    protected function getGrantedCategoryIds(User $user)
    {
        $qb = $this->catOwnershipRepo->createQueryBuilder('o');

        $qb
            ->join('o.category', 'category')
            ->select('category.id')
            ->where(
                $qb->expr()->in('o.role', ':roles')
            )
            ->setParameter('roles', $user->getRoles());

        $result = $qb->getQuery()->getResult(AbstractQuery::HYDRATE_ARRAY);

        $grantedCategoryIds = [];
        foreach ($result as $row) {
            $grantedCategoryIds[] = $row['id'];
        }

        return $grantedCategoryIds;
    }
}
