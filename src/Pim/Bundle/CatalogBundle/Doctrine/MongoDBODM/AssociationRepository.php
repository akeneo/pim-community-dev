<?php

namespace Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM;

use Doctrine\ORM\EntityManager;
use Doctrine\ODM\MongoDB\DocumentRepository;
use Pim\Bundle\CatalogBundle\Entity\AssociationType;
use Pim\Bundle\CatalogBundle\Entity\Repository\ReferableEntityRepositoryInterface;
use Pim\Bundle\CatalogBundle\Model\AssociationRepositoryInterface;

/**
 * Association repository
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AssociationRepository extends DocumentRepository implements AssociationRepositoryInterface,
 ReferableEntityRepositoryInterface
{
    /**
     * ORM EntityManager to access ORM entities
     *
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * Set the EntityManager
     *
     * @param EntityManager $entityManager
     *
     * @return ProductRepository $this
     */
    public function setEntityManager(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }
    /**
     * {@inheritdoc}
     */
    public function countForAssociationType(AssociationType $associationType)
    {
        return "12345";
    }

    /**
     * {@inheritdoc}
     */
    public function findByReference($code)
    {
        throw new \Exception('Not yet implemented');
    }

    /**
     * {@inheritdoc}
     */
    public function getReferenceProperties()
    {
        return array('owner', 'associationType');
    }
}
