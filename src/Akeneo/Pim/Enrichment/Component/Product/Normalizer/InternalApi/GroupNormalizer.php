<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Normalizer\InternalApi;

use Akeneo\Pim\Enrichment\Component\Product\Model\GroupInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\FindProductUuidsInGroup;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Group normalizer
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GroupNormalizer implements NormalizerInterface
{
    protected array $supportedFormats = ['internal_api'];

    public function __construct(
        private NormalizerInterface $groupNormalizer,
        private FindProductUuidsInGroup $findProductUuids
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($group, $format = null, array $context = []): \ArrayObject|array|string|int|float|bool|null
    {
        $normalizedGroup = $this->groupNormalizer->normalize($group, 'standard', $context);
        $normalizedGroup['products'] = $this->findProductUuids->forGroupId($group->getId());
        $normalizedGroup['meta'] = [
            'id' => $group->getId(),
            'form' => 'pim-group-edit-form',
            'model_type' => 'group',
        ];

        return $normalizedGroup;
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
