<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\WorkOrganization\Workflow\Component\Normalizer\Standard;

use Symfony\Component\Serializer\Normalizer\CacheableSupportsMethodInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Webmozart\Assert\Assert;

class UserNormalizer implements NormalizerInterface, CacheableSupportsMethodInterface
{
    private NormalizerInterface $baseUserNormalizer;

    public function __construct(NormalizerInterface $baseUserNormalizer)
    {
        $this->baseUserNormalizer = $baseUserNormalizer;
    }

    public function normalize($user, $format = null, array $context = []): array
    {
        $normalized = $this->baseUserNormalizer->normalize($user, $format, $context);

        Assert::isArray($normalized);
        return $normalized + [
                'proposals_state_notifications' => $user->getProperty('proposals_state_notifications'),
                'proposals_to_review_notification' => $user->getProperty('proposals_to_review_notification'),
            ];
    }

    public function supportsNormalization($data, $format = null): bool
    {
        return $this->baseUserNormalizer->supportsNormalization($data, $format);
    }

    public function hasCacheableSupportsMethod(): bool
    {
        return true;
    }
}
