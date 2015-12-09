<?php

namespace Pim\Bundle\EnrichBundle\Normalizer;

use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Validator\ConstraintViolationInterface;

/**
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductViolationNormalizer implements NormalizerInterface
{
    /** @var array */
    protected $supportedFormats = ['internal_api'];

    /**
     * {@inheritdoc}
     */
    public function normalize($violation, $format = null, array $context = [])
    {
        $path = $violation->getPropertyPath();

        if (0 === strpos($path, 'values')) {
            $codeStart     = strpos($path, '[') + 1;
            $codeLength    = strpos($path, ']') - $codeStart;
            $attributePath = substr($path, $codeStart, $codeLength);

            $product      = $violation->getRoot();
            $productValue = $product->getValues()[$attributePath];

            $normalizedViolation = [
                'attribute' => $productValue->getAttribute()->getCode(),
                'locale'    => $productValue->getLocale(),
                'scope'     => $productValue->getScope(),
                'message'   => $violation->getMessage()
            ];
        } else {
            $normalizedViolation = [
                'global'  => true,
                'message' => $violation->getMessage()
            ];
        }

        return $normalizedViolation;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof ConstraintViolationInterface && in_array($format, $this->supportedFormats);
    }
}
