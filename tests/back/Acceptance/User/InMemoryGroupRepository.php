<?php

declare(strict_types=1);

namespace Akeneo\Test\Acceptance\User;

use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Component\StorageUtils\Saver\SaverInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Collections\Selectable;
use Doctrine\Common\Persistence\ObjectRepository;
use Oro\Bundle\UserBundle\Entity\Group;
use Pim\Component\User\Model\GroupInterface;

/**
 * @author    Arnaud Langlade <arnaud.langlade@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class InMemoryGroupRepository implements
    IdentifiableObjectRepositoryInterface,
    ObjectRepository,
    Selectable,
    SaverInterface
{
    /** @var Group[] */
    private $groups;

    /** @var string */
    private $className;

    /**
     * @param string $className
     */
    public function __construct(string $className)
    {
        $this->groups = new ArrayCollection();
        $this->className = $className;
    }

    public function save($group, array $options = [])
    {
        if (!$group instanceof GroupInterface) {
            throw new \InvalidArgumentException('Only user group objects are supported.');
        }
        $this->groups->set($group->getName(), $group);
    }

    /**
     * {{@inheritdoc}}
     */
    public function getIdentifierProperties()
    {
        return ['name'];
    }

    /**
     * {{@inheritdoc}}
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
    public function matching(Criteria $criteria)
    {
        throw new NotImplementedException(__METHOD__);
    }
}
