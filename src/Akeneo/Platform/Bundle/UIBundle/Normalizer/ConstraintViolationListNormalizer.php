<?php

namespace Akeneo\Platform\Bundle\UIBundle\Normalizer;

use Symfony\Component\Serializer\Normalizer\CacheableSupportsMethodInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;

/**
 * @author    Julien Janvier <j.janvier@gmail.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class ConstraintViolationListNormalizer implements NormalizerInterface, CacheableSupportsMethodInterface
{
    /** @var NormalizerInterface */
    private $normalizer;

    public function __construct(NormalizerInterface $normalizer)
    {
        $this->normalizer = $normalizer;
    }

    public function normalize($constraintList, $format = null, array $context = [])
    {
        $result = [];

        foreach ($constraintList as $constraint) {
            $result[] = $this->normalizer->normalize($constraint, $format, $context);
        }

        return $result;
    }

    /**
     * @inheritDoc
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof ConstraintViolationListInterface;
    }

    public function hasCacheableSupportsMethod(): bool
    {
        return true;
    }
}
