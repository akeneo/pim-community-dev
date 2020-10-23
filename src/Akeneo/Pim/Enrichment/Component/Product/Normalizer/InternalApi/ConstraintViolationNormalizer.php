<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Normalizer\InternalApi;

use Doctrine\Common\Inflector\Inflector;
use Symfony\Component\Serializer\Normalizer\CacheableSupportsMethodInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Validator\ConstraintViolation;
use Webmozart\Assert\Assert;

/**
 * @author    Yohan Blain <yohan.blain@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class ConstraintViolationNormalizer implements NormalizerInterface, CacheableSupportsMethodInterface
{
    /** @var string[] */
    protected $supportedFormats = ['internal_api'];

    /**
     * {@inheritdoc}
     */
    public function normalize($violation, $format = null, array $context = [])
    {
        Assert::isInstanceOf($violation, ConstraintViolation::class);
        $path = $this->getStandardPath($violation);
        $translate = (bool)($context['translate'] ?? true);

        if (!$translate) {
            return [
                'messageTemplate' => $violation->getMessageTemplate(),
                'parameters' => $violation->getParameters(),
                'message' => $violation->getMessage(),
                'propertyPath' => $path,
                'invalidValue' => $violation->getInvalidValue(),
            ];
        }

        if (null === $path || '' === $path) {
            return [
                'message' => $violation->getMessage(),
                'global'  => true,
            ];
        }

        return [
            'path'    => $path,
            'message' => $violation->getMessage(),
            'global'  => false,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof ConstraintViolation && in_array($format, $this->supportedFormats);
    }

    public function hasCacheableSupportsMethod(): bool
    {
        return true;
    }

    /**
     * Returns the field concerned by the violation. It must be standard format valid.
     * If a name has been set in the constraint payload it is used, else it fallbacks on a tableized version of the
     * entity property (example: 'metricFamily' -> 'metric_family').
     * If the constraint is global and has no explicit path defined in its payload, it returns null.
     *
     * @param ConstraintViolation $violation
     *
     * @return string|null
     */
    protected function getStandardPath(ConstraintViolation $violation)
    {
        $constraint = $violation->getConstraint();

        $shouldNormalizePropertyPath = (bool)($constraint->payload['normalize_property_path'] ?? true);
        if (!$shouldNormalizePropertyPath) {
            return $violation->getPropertyPath();
        }

        if (null !== $constraint && isset($constraint->payload['standardPropertyName'])) {
            return $constraint->payload['standardPropertyName'];
        }

        if (null === $violation->getPropertyPath()) {
            return null;
        }

        return Inflector::tableize($violation->getPropertyPath());
    }
}
