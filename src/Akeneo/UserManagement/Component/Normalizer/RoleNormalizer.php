<?php
declare(strict_types=1);

namespace Akeneo\UserManagement\Component\Normalizer;

use Akeneo\UserManagement\Component\Model\RoleInterface;
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
    /** @var string[] */
    protected array $supportedFormats = ['array', 'standard'];

    /**
     * {@inheritdoc}
     */
    public function normalize($role, $format = null, array $context = [])
    {
        Assert::isInstanceOf($role, RoleInterface::class);

        $permissions = [];

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
}
