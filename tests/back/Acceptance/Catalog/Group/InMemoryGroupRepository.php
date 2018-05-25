<?php

declare(strict_types=1);

namespace Akeneo\Test\Acceptance\Catalog\Group;

use Akeneo\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\Test\Common\NotImplementedException;
use Doctrine\Common\Collections\ArrayCollection;
use Pim\Bundle\CatalogBundle\Entity\Group;
use Pim\Component\Catalog\Model\GroupInterface;
use Pim\Component\Catalog\Repository\GroupRepositoryInterface;

/**
 * @author    Arnaud Langlade <arnaud.langlade@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class InMemoryGroupRepository implements GroupRepositoryInterface, SaverInterface
{
    /** @var Group[] */
    private $groups;

    public function __construct()
    {
        $this->groups = new ArrayCollection();
    }

    public function save($group, array $options = [])
    {
        if(!$group instanceof GroupInterface) {
            throw new \InvalidArgumentException('Only group objects are supported.');
        }
        $this->groups->set($group->getCode(), $group);
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
        return $this->groups->get($identifier);
    }

    /**
     * {@inheritdoc}
     */
    public function createAssociationDatagridQueryBuilder()
    {
        throw new NotImplementedException(__METHOD__);
    }

    /**
     * {@inheritdoc}
     */
    public function getOptions($dataLocale, $collectionId = null, $search = '', array $options = [])
    {
        throw new NotImplementedException(__METHOD__);
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
        throw new NotImplementedException(__METHOD__);
    }
}
