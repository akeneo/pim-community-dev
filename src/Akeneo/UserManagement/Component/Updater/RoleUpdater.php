<?php

namespace Akeneo\UserManagement\Component\Updater;

use Akeneo\Tool\Component\StorageUtils\Exception\InvalidObjectException;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Akeneo\UserManagement\Component\Model\RoleInterface;
use Akeneo\UserManagement\Component\Model\User;
use Doctrine\Common\Util\ClassUtils;
use Oro\Bundle\SecurityBundle\Acl\Persistence\AclManager;

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
     *     'label': 'Administrator',
     * }
     */
    public function update($role, array $data, array $options = [])
    {
        if (!$role instanceof RoleInterface) {
            throw InvalidObjectException::objectExpected(
                ClassUtils::getClass($role),
                RoleInterface::class
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
     * @param RoleInterface $role
     * @param string        $field
     * @param mixed         $data
     *
     * @throws \InvalidArgumentException
     */
    protected function setData(RoleInterface $role, $field, $data)
    {
        switch ($field) {
            case 'role':
                if (empty($role->getRole())) {
                    $role->setRole($data);
                }
                break;
            case 'label':
                $role->setLabel($data);
                break;
        }
    }

    /**
     * Load the ACL per role
     *
     * @param RoleInterface $role
     */
    protected function loadAcls(RoleInterface $role)
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
