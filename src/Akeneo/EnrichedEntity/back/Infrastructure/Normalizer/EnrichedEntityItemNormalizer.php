<?php

declare(strict_types=1);

namespace Akeneo\EnrichedEntity\back\Infrastructure\Normalizer;

use Akeneo\EnrichedEntity\back\Application\EnrichedEntity\EnrichedEntityList\EnrichedEntityItem;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class EnrichedEntityItemNormalizer implements NormalizerInterface
{
    /** @var string[] */
    private $supportedFormats = ['internal_api'];

    /**
     * {@inheritdoc}
     *
     * @param EnrichedEntityItem $enrichedEntityItem
     */
    public function normalize($enrichedEntityItem, $format = null, array $context = []): array
    {
        $normalizedEnrichedEntity = [
            'identifier' => $enrichedEntityItem->identifier,
            'labels'     => $enrichedEntityItem->labels
        ];

        return $normalizedEnrichedEntity;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null): bool
    {
        return $data instanceof EnrichedEntityItem && in_array($format, $this->supportedFormats);
    }
}
