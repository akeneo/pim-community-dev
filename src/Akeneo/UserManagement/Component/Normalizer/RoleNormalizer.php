<?php
declare(strict_types=1);

namespace Akeneo\UserManagement\Component\Normalizer;

use Akeneo\UserManagement\Component\Model\RoleInterface;
use Oro\Bundle\SecurityBundle\Acl\AccessLevel;
use Oro\Bundle\SecurityBundle\Acl\Persistence\AclManager;
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

    private array $supportedFormats = ['array', 'standard'];

    private AclManager $aclManager;
    private NormalizerInterface $aclPrivilegeNormalizer;
    private ?array $cacheIndexedAclIds = null;

    public function __construct(AclManager $aclManager, NormalizerInterface $aclPrivilegeNormalizer)
    {
        $this->aclManager = $aclManager;
        $this->aclPrivilegeNormalizer = $aclPrivilegeNormalizer;
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
            if (!array_key_exists($privilege->getIdentity()->getId(), $indexedAclIds)) {
                continue;
            }

            $aclPermissions = $privilege->getPermissions();
            if (count($aclPermissions) > 1) {
                continue;
            }

            $aclPermission = $aclPermissions->current();
            if ($aclPermission->getAccessLevel() !== AccessLevel::NONE_LEVEL) {
                $permissions[] = $this->aclPrivilegeNormalizer->normalize($privilege, $format, $context);
            }
        }

        return [
            'role' => $role->getRole(),
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
                if ($extensionKey !== static::ACL_EXTENSION_KEY) {
                    continue;
                }

                foreach ($extension->getClasses() as $aclClassInfo) {
                    $this->cacheIndexedAclIds[sprintf('%s:%s', $extensionKey, $aclClassInfo->getClassName())] = true;
                }
            }
        }

        return $this->cacheIndexedAclIds;
    }
}
