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
        // TODO
        return [];
    }
}
