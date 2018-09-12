<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Normalizer\InternalApi;

use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GroupViolationNormalizer implements NormalizerInterface
{
    /** @var array */
    protected $supportedFormats = ['internal_api'];

    /** @var NormalizerInterface */
    protected $normalizer;

    /**
     * @param NormalizerInterface $normalizer
     */
    public function __construct(NormalizerInterface $normalizer)
    {
        $this->normalizer = $normalizer;
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($violations, $format = null, array $context = [])
    {
        $normalizedViolations = [];
        $accessor = PropertyAccess::createPropertyAccessor();

        foreach ($violations as $violation) {
            $path = $violation->getPropertyPath();
            if (0 === strpos($path, 'translations')) {
                $propertyPath = str_replace('.label', '', $path);

                $translation = $accessor->getValue($violation->getRoot(), $propertyPath);

                $normalizedViolations['translations'][$translation->getLocale()] = [
                    'locale'  => $translation->getLocale(),
                    'message' => $violation->getMessage()
                ];
            } else {
                $normalizedViolations['values'][] = $this->normalizer->normalize($violation, $format, $context);
            }
        }

        return $normalizedViolations;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return false;
    }
}
