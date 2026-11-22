<?php
declare(strict_types=1);

namespace Akeneo\UserManagement\Component\Normalizer;

use Akeneo\UserManagement\Component\Model\GroupInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Webmozart\Assert\Assert;

/**
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
final class GroupNormalizer implements NormalizerInterface
{
    /** @var string[] */
    protected array $supportedFormats = ['array', 'standard'];

    /**
     * {@inheritdoc}
     */
    public function normalize($group, $format = null, array $context = []): \ArrayObject|array|string|int|float|bool|null
    {
        Assert::isInstanceOf($group, GroupInterface::class);

        return [
            'name' => $group->getName(),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null, array $context = []): bool
    {
        return $data instanceof GroupInterface && in_array($format, $this->supportedFormats);
    }

    public function getSupportedTypes(?string $format): array
    {
        return ['*' => true];
    }
}
