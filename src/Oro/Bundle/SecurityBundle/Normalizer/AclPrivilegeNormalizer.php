<?php
declare(strict_types=1);

namespace Oro\Bundle\SecurityBundle\Normalizer;

use Oro\Bundle\SecurityBundle\Model\AclPermission;
use Oro\Bundle\SecurityBundle\Model\AclPrivilege;
use Symfony\Component\Serializer\Normalizer\CacheableSupportsMethodInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Webmozart\Assert\Assert;

/**
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
final class AclPrivilegeNormalizer implements NormalizerInterface, CacheableSupportsMethodInterface
{
    protected array $supportedFormats = ['array', 'standard'];

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null): bool
    {
        return $data instanceof AclPrivilege && in_array($format, $this->supportedFormats);
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($privilege, $format = null, array $context = [])
    {
        Assert::isInstanceOf($privilege, AclPrivilege::class);

        $permissionsForPrivilege = array_map(
            fn (AclPermission $aclPermission) => [
                'name' => $aclPermission->getName(),
                'access_level' => $aclPermission->getAccessLevel(),
            ],
            $privilege->getPermissions()->toArray()
        );

        return [
            'id' => $privilege->getIdentity()->getId(),
            'name' => $privilege->getIdentity()->getName(),
            'group' => $privilege->getGroup(),
            'type' => $privilege->getExtensionKey(),
            'permissions' => $permissionsForPrivilege,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function hasCacheableSupportsMethod(): bool
    {
        return true;
    }
}
