<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\EnrichedEntity\back\Infrastructure\Normalizer;

use Akeneo\EnrichedEntity\back\Domain\Query\RecordItem;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class RecordItemNormalizer implements NormalizerInterface
{
    /** @var string[] */
    private $supportedFormats = ['internal_api'];

    /**
     * {@inheritdoc}
     *
     * @param RecordItem $recordItem
     */
    public function normalize($recordItem, $format = null, array $context = []): array
    {
        $normalizedRecordItem = [
            'identifier'                 => $recordItem->identifier,
            'enriched_entity_identifier' => $recordItem->enrichedEntityIdentifier,
            'labels'                     => $recordItem->labels,
        ];

        return $normalizedRecordItem;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null): bool
    {
        return $data instanceof RecordItem && in_array($format, $this->supportedFormats);
    }
}
