<?php

namespace Pim\Component\User\Updater;

use Akeneo\Component\StorageUtils\Exception\InvalidObjectException;
use Akeneo\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Doctrine\Common\Util\ClassUtils;
use Oro\Bundle\SecurityBundle\Acl\Persistence\AclManager;
use Oro\Bundle\UserBundle\Entity\Role;
use Pim\Bundle\UserBundle\Entity\User;

/**
 * Updates a role
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class RoleUpdater implements ObjectUpdaterInterface
{
    /** @var AclManager */
    protected $aclManager;

    /**
     * @param AclManager $aclManager
     */
    public function __construct(AclManager $aclManager)
    {
        $this->aclManager = $aclManager;
    }

    /**
     * {@inheritdoc}
     *
     * Expected input format :
     * {
     *     'role': 'ROLE_ADMINISTRATOR',
     *     'name': 'Administrator',
     * }
     */
    public function update($role, array $data, array $options = [])
    {
        if (!$role instanceof Role) {
            throw InvalidObjectException::objectExpected(
                ClassUtils::getClass($role),
                'Oro\Bundle\UserBundle\Entity\Role'
            );
        }

        foreach ($data as $field => $value) {
            $this->setData($role, $field, $value);
        }

        $this->loadAcls($role);
        $this->aclManager->flush();

        return $this;
    }

    /**
     * @param Role   $role
     * @param string $field
     * @param mixed  $data
     *
     * @throws \InvalidArgumentException
     */
    protected function setData(Role $role, $field, $data)
    {
        switch ($field) {
            case 'role':
                $role->setRole($data);
                break;
            case 'label':
                $role->setLabel($data);
                break;
        }
    }

    /**
     * Load the ACL per role
     *
     * @param Role $role
     */
    protected function loadAcls(Role $role)
    {
        if (User::ROLE_ANONYMOUS === $role->getRole()) {
            return;
        }

        $sid = $this->aclManager->getSid($role);

        foreach ($this->aclManager->getAllExtensions() as $extension) {
            $rootOid = $this->aclManager->getRootOid($extension->getExtensionKey());
            foreach ($extension->getAllMaskBuilders() as $maskBuilder) {
                $fullAccessMask = $maskBuilder->hasConst('GROUP_SYSTEM')
                    ? $maskBuilder->getConst('GROUP_SYSTEM')
                    : $maskBuilder->getConst('GROUP_ALL');
                $this->aclManager->setPermission($sid, $rootOid, $fullAccessMask, true);
            }
        }
    }
}
