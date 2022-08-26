<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2022 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Platform\TailoredImport\Infrastructure\Normalizer;

use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;

class FileConstraintViolationListNormalizer implements NormalizerInterface
{
    /**
     * @param ConstraintViolationListInterface $object
     *
     * @return array<int, array<string, string|array<string, string>>>
     */
    public function normalize($object, string $format = null, array $context = []): array
    {
        if (0 === $object->count()) {
            return [];
        }

        return array_map(
            static fn (ConstraintViolationInterface $violation) => [
                'propertyPath' => $violation->getPropertyPath(),
                'message' => (string) $violation->getMessage(),
                'messageTemplate' => $violation->getMessageTemplate(),
                'parameters' => $violation->getParameters(),
            ],
            iterator_to_array($object),
        );
    }

    public function supportsNormalization($data, string $format = null): bool
    {
        return $data instanceof ConstraintViolationListInterface;
    }
}
