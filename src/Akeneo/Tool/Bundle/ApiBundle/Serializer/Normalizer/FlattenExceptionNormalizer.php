<?php

declare(strict_types=1);

namespace Akeneo\Tool\Bundle\ApiBundle\Serializer\Normalizer;

use FOS\RestBundle\Serializer\Normalizer\FlattenExceptionNormalizer as FosRestNormalizer;
use Symfony\Component\ErrorHandler\Exception\FlattenException;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * We have to decorate the default internal FoS Rest Bundle FlattenException normalizer because it is not compatible
 * with our API normalizers like DocumentedNormalizer and ViolationNormalizer.
 * These normalizers hold "metadata" (like the violations) that are lost
 * when using the default FlattenExceptionNormalizer.
 *
 * @author JMLeroux <jean-marie.leroux@akeneo.com>
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
final class FlattenExceptionNormalizer implements NormalizerInterface
{
    private FosRestNormalizer $fosRestNormalizer;
    private NormalizerInterface $normalizer;

    public function __construct(FosRestNormalizer $fosRestNormalizer)
    {
        $this->fosRestNormalizer = $fosRestNormalizer;
    }

    /**
     * The normalizer has to be injected via a setter
     * because injecting it via the constructor would lead to a circular reference error.
     */
    public function setNormalizer(NormalizerInterface $normalizer): void
    {
        $this->normalizer = $normalizer;
    }

    public function normalize($exception, $format = null, array $context = [])
    {
        $contextException = $context['exception'] ?? null;
        if ($this->normalizer->supportsNormalization($contextException, $format)) {
            return $this->normalizer->normalize($contextException, $format, $context);
        }

        return $this->fosRestNormalizer->normalize($exception, $format, $context);
    }

    public function supportsNormalization($data, $format = null): bool
    {
        return $data instanceof FlattenException;
    }
}
