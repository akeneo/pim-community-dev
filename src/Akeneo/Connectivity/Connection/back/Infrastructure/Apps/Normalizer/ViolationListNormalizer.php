<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Apps\Normalizer;

use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ViolationListNormalizer implements NormalizerInterface
{
    /**
     * {@inheritdoc}
     * @return array<int, array{message: string, property_path: string}>
     */
    public function normalize($object, ?string $format = null, array $context = []): array
    {
        if (!$object instanceof ConstraintViolationListInterface) {
            throw new \InvalidArgumentException();
        }

        $violations = [];

        /** @var ConstraintViolationInterface $violation */
        foreach ($object as $violation) {
            $violations[] = [
                'message' => $violation->getMessageTemplate(),
                'property_path' => $violation->getPropertyPath(),
            ];
        }

        return $violations;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, ?string $format = null): bool
    {
        return $data instanceof ConstraintViolationListInterface;
    }
}
