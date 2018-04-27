<?php

declare(strict_types=1);

namespace Akeneo\Test\Acceptance\AssociationType;

use Akeneo\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\Test\Acceptance\Common\NotImplementedException;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Pim\Component\Catalog\Model\AssociationAwareInterface;
use Pim\Component\Catalog\Model\AssociationTypeInterface;
use Pim\Component\Catalog\Repository\AssociationTypeRepositoryInterface;

/**
 * @author    Damien Carcel <damien.carcel@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class InMemoryAssociationTypeRepository implements AssociationTypeRepositoryInterface, SaverInterface
{
    /** @var Collection */
    private $associationTypes;

    /** @var string */
    private $className;

    /**
     * @param array  $associationTypes
     * @param string $className
     */
    public function __construct(array $associationTypes, string $className)
    {
        $this->associationTypes = new ArrayCollection($associationTypes);
        $this->className = $className;
    }

    /**
     * {@inheritdoc}
     */
    public function findMissingAssociationTypes(AssociationAwareInterface $entity)
    {
        throw new NotImplementedException(__METHOD__);
    }

    /**
     * {@inheritdoc}
     */
    public function countAll(): int
    {
        throw new NotImplementedException(__METHOD__);
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentifierProperties()
    {
        return ['code'];
    }

    /**
     * {@inheritdoc}
     */
    public function findOneByIdentifier($identifier)
    {
        return $this->associationTypes->get($identifier);
    }

    /**
     * {@inheritdoc}
     */
    public function find($id)
    {
        throw new NotImplementedException(__METHOD__);
    }

    /**
     * {@inheritdoc}
     */
    public function findAll()
    {
        throw new NotImplementedException(__METHOD__);
    }

    /**
     * {@inheritdoc}
     */
    public function findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
    {
        throw new NotImplementedException(__METHOD__);
    }

    /**
     * {@inheritdoc}
     */
    public function findOneBy(array $criteria)
    {
        throw new NotImplementedException(__METHOD__);
    }

    /**
     * {@inheritdoc}
     */
    public function getClassName()
    {
        return $this->className;
    }

    /**
     * {@inheritdoc}
     */
    public function save($associationType, array $options = [])
    {
        if (!$associationType instanceof AssociationTypeInterface) {
            throw new \InvalidArgumentException('The object argument should be an association type');
        }

        $this->associationTypes->set($associationType->getCode(), $associationType);
    }
}
