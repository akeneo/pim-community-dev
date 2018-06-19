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

use Akeneo\EnrichedEntity\back\Application\EnrichedEntity\EnrichedEntityDetails\EnrichedEntityDetails;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class EnrichedEntityDetailsNormalizer implements NormalizerInterface
{
    /** @var string[] */
    private $supportedFormats = ['internal_api'];

    /**
     * {@inheritdoc}
     *
     * @param EnrichedEntityDetails $enrichedEntityDetails
     */
    public function normalize($enrichedEntityDetails, $format = null, array $context = []): array
    {
        $normalizedEnrichedEntity = [
            'identifier' => $enrichedEntityDetails->identifier,
            'labels'     => $enrichedEntityDetails->labels
        ];

        return $normalizedEnrichedEntity;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null): bool
    {
        return $data instanceof EnrichedEntityDetails && in_array($format, $this->supportedFormats);
    }
}
