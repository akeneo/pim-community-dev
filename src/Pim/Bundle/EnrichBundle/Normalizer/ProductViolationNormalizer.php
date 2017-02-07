<?php

namespace Pim\Bundle\EnrichBundle\Normalizer;

use Pim\Component\Catalog\Repository\AttributeRepositoryInterface;
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

    /** @var AttributeRepositoryInterface */
    protected $attributeRepository;

    /**
     * @param AttributeRepositoryInterface $attributeRepository
     */
    public function __construct(AttributeRepositoryInterface $attributeRepository)
    {
        $this->attributeRepository = $attributeRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($violation, $format = null, array $context = [])
    {
        $propertyPath = $violation->getPropertyPath();

        if (0 === strpos($propertyPath, 'values')) {
            if (!isset($context['product'])) {
                throw new \InvalidArgumentException('Expects a product context');
            }

            $codeStart = strpos($propertyPath, '[') + 1;
            $codeLength = strpos($propertyPath, ']') - $codeStart;
            $attributeProperties = json_decode(substr($propertyPath, $codeStart, $codeLength), true);

            $normalizedViolation = [
                'attribute' => $attributeProperties['code'],
                'locale'    => $attributeProperties['locale'],
                'scope'     => $attributeProperties['scope'],
                'message'   => $violation->getMessage(),
            ];
        } elseif ('identifier' === $propertyPath) {
            $normalizedViolation = [
                'attribute' => $this->attributeRepository->getIdentifierCode(),
                'locale'    => null,
                'scope'     => null,
                'message'   => $violation->getMessage(),
            ];
        } else {
            $normalizedViolation = [
                'global'  => true,
                'message' => $violation->getMessage(),
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
