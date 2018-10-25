<?php

declare(strict_types=1);

namespace Akeneo\Test\Acceptance\User;

use Akeneo\Test\Acceptance\Common\NotImplementedException;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\UserManagement\Component\Model\GroupInterface;
use Akeneo\UserManagement\Component\Repository\GroupRepositoryInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Persistence\ObjectRepository;

/**
 * @author    Arnaud Langlade <arnaud.langlade@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class InMemoryGroupRepository implements GroupRepositoryInterface, SaverInterface, ObjectRepository
{
    /** @var GroupInterface[] */
    private $groups;

    public function __construct()
    {
        $this->groups = new ArrayCollection();
    }

    public function save($group, array $options = [])
    {
        if (!$group instanceof GroupInterface) {
            throw new \InvalidArgumentException('Only user group objects are supported.');
        }
        $this->groups->set($group->getName(), $group);
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentifierProperties()
    {
        return ['name'];
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
    public function find($id)
    {
        throw new NotImplementedException();
    }

    /**
     * {@inheritdoc}
     */
    public function findAll()
    {
        throw new NotImplementedException();
    }

    /**
     * {@inheritdoc}
     */
    public function findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
    {
        throw new NotImplementedException();
    }

    /**
     * {@inheritdoc}
     */
    public function findOneBy(array $criteria)
    {
        throw new NotImplementedException();
    }

    /**
     * {@inheritdoc}
     */
    public function getClassName()
    {
        throw new NotImplementedException();
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultUserGroup()
    {
        throw new NotImplementedException();
    }
}
