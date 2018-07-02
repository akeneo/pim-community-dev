<?php

declare(strict_types=1);

namespace Akeneo\Test\Acceptance\User;

use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\UserManagement\Component\Model\RoleInterface;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @author    Arnaud Langlade <arnaud.langlade@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class InMemoryRoleRepository implements IdentifiableObjectRepositoryInterface, SaverInterface
{
    /** @var RoleInterface[] */
    private $roles;

    public function __construct()
    {
        $this->roles = new ArrayCollection();
    }

    public function save($role, array $options = [])
    {
        if (!$role instanceof RoleInterface) {
            throw new \InvalidArgumentException('Only user role objects are supported.');
        }
        $index = $role->getRole();
        $index = strtolower(str_replace('ROLE_', '', $index));
        $this->roles->set($index, $role);
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentifierProperties()
    {
        return ['role'];
    }

    /**
     * {@inheritdoc}
     */
    public function findOneByIdentifier($identifier)
    {
        return $this->roles->get($identifier);
    }
}
