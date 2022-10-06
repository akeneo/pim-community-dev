<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\AssetManager\Infrastructure\Normalizer;

use Symfony\Component\Serializer\Normalizer\CacheableSupportsMethodInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\ConstraintViolationList;

/**
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2019 Akeneo SAS (https://www.akeneo.com)
 */
class ConstraintViolationListNormalizer implements NormalizerInterface, CacheableSupportsMethodInterface
{
    public function __construct(private NormalizerInterface $normalizer)
    {
    }

    public function hasCacheableSupportsMethod(): bool
    {
        return true;
    }

    public function normalize($constraintViolationList, $format = null, array $context = [])
    {
        $normalizedViolations = [];

        /** @var ConstraintViolationInterface $constraintViolation */
        foreach ($constraintViolationList as $constraintViolation) {
            $normalizedViolations[] = [
                'messageTemplate' => $constraintViolation->getMessageTemplate(),
                'parameters' => $constraintViolation->getParameters(),
                'message' => $constraintViolation->getMessage(),
                'propertyPath' => $constraintViolation->getPropertyPath(),
                'invalidValue' => $this->normalizeValue($constraintViolation->getInvalidValue()),
            ];
        }

        return $normalizedViolations;
    }

    private function normalizeValue($value)
    {
        try {
            return $this->normalizer->normalize($value);
        } catch (\Exception) {
            return null;
        }
    }

    public function supportsNormalization($data, $format = null): bool
    {
        return $data instanceof ConstraintViolationList;
    }
}
