<?php
declare(strict_types=1);

namespace Akeneo\UserManagement\Component\Normalizer;

use Akeneo\UserManagement\Component\Model\RoleInterface;
use Oro\Bundle\SecurityBundle\Acl\Persistence\AclManager;
use Oro\Bundle\SecurityBundle\Model\AclPermission;
use Symfony\Component\Serializer\Normalizer\CacheableSupportsMethodInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Webmozart\Assert\Assert;

/**
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
final class RoleNormalizer implements NormalizerInterface, CacheableSupportsMethodInterface
{
    private const ACL_EXTENSION_KEY = 'action';

    protected array $supportedFormats = ['array', 'standard'];

    private AclManager $aclManager;
    private ?array $cacheIndexedAclIds = null;

    public function __construct(AclManager $aclManager)
    {
        $this->aclManager = $aclManager;
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($role, $format = null, array $context = [])
    {
        Assert::isInstanceOf($role, RoleInterface::class);

        $privileges = $this->aclManager->getPrivilegeRepository()->getPrivileges(
            $this->aclManager->getSid($role)
        );

        $permissions = [];
        $indexedAclIds = $this->getIndexedAclIds();
        foreach ($privileges as $privilege) {
            if (static::ACL_EXTENSION_KEY !== $privilege->getExtensionKey()) {
                continue;
            }

            if (!array_key_exists($privilege->getIdentity()->getId(), $indexedAclIds)) {
                continue;
            }

            $permissionsForPrivilege = array_map(
                fn(AclPermission $aclPermission) => [
                    'name' => $aclPermission->getName(),
                    'access_level' => $aclPermission->getAccessLevel(),
                ],
                $privilege->getPermissions()->toArray()
            );
            $permissions[] = [
                'id' => $privilege->getIdentity()->getId(),
                'name' => $privilege->getIdentity()->getName(),
                'group' => $privilege->getGroup(),
                'type' => $privilege->getExtensionKey(),
                'permissions' => $permissionsForPrivilege,
            ];
        }

        return [
            'label' => $role->getLabel(),
            'permissions' => $permissions,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null): bool
    {
        return $data instanceof RoleInterface && in_array($format, $this->supportedFormats);
    }

    /**
     * {@inheritdoc}
     */
    public function hasCacheableSupportsMethod(): bool
    {
        return true;
    }

    private function getIndexedAclIds(): array
    {
        if (null === $this->cacheIndexedAclIds) {
            $this->cacheIndexedAclIds = [];
            foreach ($this->aclManager->getAllExtensions() as $extension) {
                $extensionKey = $extension->getExtensionKey();

                foreach ($extension->getClasses() as $aclClassInfo) {
                    $this->cacheIndexedAclIds[sprintf('%s:%s', $extensionKey, $aclClassInfo->getClassName())] = true;
                }
            }
        }

        return $this->cacheIndexedAclIds;
    }
}
