<?php

declare(strict_types=1);

namespace Akeneo\Test\Acceptance\User;

use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Component\StorageUtils\Saver\SaverInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Collections\Selectable;
use Doctrine\Common\Persistence\ObjectRepository;
use Oro\Bundle\UserBundle\Entity\Role;
use Pim\Bundle\UserBundle\Entity\UserInterface;
use Pim\Bundle\UserBundle\Repository\UserRepositoryInterface;
use Symfony\Component\Security\Core\Role\RoleInterface;

/**
 * @author    Arnaud Langlade <arnaud.langlade@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class InMemoryRoleRepository implements
    IdentifiableObjectRepositoryInterface,
    ObjectRepository,
    Selectable,
    SaverInterface
{
    /** @var Role[] */
    private $roles;

    /** @var string */
    private $className;

    /**
     * @param string $className
     */
    public function __construct(string $className)
    {
        $this->roles = new ArrayCollection();
        $this->className = $className;
    }

    public function save($role, array $options = [])
    {
        if(!$role instanceof RoleInterface) {
            throw new \InvalidArgumentException('Only user role objects are supported.');
        }
        $index = $role->getRole();
        $index = strtolower(str_replace('ROLE_', '', $index));
        $this->roles->set($index, $role);
    }

    /**
     * {{@inheritdoc}}
     */
    public function getIdentifierProperties()
    {
        return ['role'];
    }

    /**
     * {{@inheritdoc}}
     */
    public function findOneByIdentifier($identifier)
    {
        return $this->roles->get($identifier);
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
