<?php

namespace Pim\Bundle\EnrichBundle\Normalizer;

use Pim\Component\Catalog\Model\Groupinterface;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Model\ProductTemplateInterface;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Validator\ConstraintViolationInterface;

/**
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class VariantGroupViolationNormalizer implements NormalizerInterface
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
        $accessor             = PropertyAccess::createPropertyAccessor();

        foreach ($violations as $violation) {
            $path = $violation->getPropertyPath();
            if (0 === strpos($path, 'translations')) {
                $path = $violation->getPropertyPath();

                $propertyPath = str_replace('.label', '', $violation->getPropertyPath());

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
