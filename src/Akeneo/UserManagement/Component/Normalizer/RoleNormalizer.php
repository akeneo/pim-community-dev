<?php
declare(strict_types=1);

namespace Akeneo\UserManagement\Component\Normalizer;

use Akeneo\UserManagement\Component\Model\RoleInterface;
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

    protected array $supportedFormats = ['array', 'standard'];

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
    public function normalize($aclPrivilege, $format = null, array $context = [])
    {
        Assert::isInstanceOf($aclPrivilege, RoleInterface::class);

        $privileges = $this->aclManager->getPrivilegeRepository()->getPrivileges(
            $this->aclManager->getSid($aclPrivilege)
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

            $permissions[] = $this->aclPrivilegeNormalizer->normalize($privilege, $format, $context);
        }

        return [
            'label' => $aclPrivilege->getLabel(),
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
