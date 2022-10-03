<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Normalizer\InternalApi;

use Akeneo\Pim\Enrichment\Component\Product\Model\GroupInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\FindProductUuidsInGroup;
use Akeneo\Platform\Bundle\UIBundle\Provider\StructureVersion\StructureVersionProviderInterface;
use Akeneo\Tool\Bundle\VersioningBundle\Manager\VersionManager;
use Symfony\Component\Serializer\Normalizer\CacheableSupportsMethodInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Group normalizer
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GroupNormalizer implements NormalizerInterface, CacheableSupportsMethodInterface
{
    protected array $supportedFormats = ['internal_api'];

    public function __construct(
        protected NormalizerInterface $groupNormalizer,
        protected StructureVersionProviderInterface $structureVersionProvider,
        protected VersionManager $versionManager,
        protected NormalizerInterface $versionNormalizer,
        private  FindProductUuidsInGroup $findProductUuids
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($group, $format = null, array $context = [])
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
    public function supportsNormalization($data, $format = null): bool
    {
        return $data instanceof GroupInterface && in_array($format, $this->supportedFormats);
    }

    public function hasCacheableSupportsMethod(): bool
    {
        return true;
    }
}
