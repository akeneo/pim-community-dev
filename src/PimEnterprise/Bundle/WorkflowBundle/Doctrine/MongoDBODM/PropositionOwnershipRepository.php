<?php

namespace PimEnterprise\Bundle\WorkflowBundle\Doctrine\MongoDBODM;

use Doctrine\ORM\AbstractQuery;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ORM\EntityManager;
use Oro\Bundle\UserBundle\Entity\User;
use PimEnterprise\Bundle\WorkflowBundle\Repository\PropositionOwnershipRepositoryInterface;
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
     * ORM EntityManager to access ORM entities
     *
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * MongoDBODM Document Manager to access ODM entities
     *
     * @var DocumentManager
     */
    protected $documentManager;

    /**
     * @var string
     */
    protected $documentName;

    /**
     * CategoryOwnership entity class
     *
     * @var string
     */
    protected $catOwnershipClass;

    /**
     * @param DocumentManager $docManager
     * @param string          $documentName
     * @param EntityManager   $entManager
     * @param string          $catOwnershipClass
     */
    public function __construct(
        DocumentManager $docManager,
        $documentName,
        EntityManager $entManager,
        $catOwnershipClass
    ) {
        $this->documentManager   = $docManager;
        $this->entityManager     = $entManager;
        $this->documentName      = $documentName;
        $this->catOwnershipClass = $catOwnershipClass;
    }

    /**
     * {@inheritdoc}
     */
    public function findApprovableByUser(User $user, $limit = null)
    {
        $qb = $this->documentManager->createQueryBuilder($this->documentName);

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
        $catOwnershipRepo = $this->entityManager->getRepository($this->catOwnershipClass);

        $qb = $catOwnershipRepo->createQueryBuilder('o');

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
