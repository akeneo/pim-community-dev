<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Normalizer\InternalApi;

use Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints\UniqueValue;
use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;
use Symfony\Component\Serializer\Normalizer\CacheableSupportsMethodInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Validator\ConstraintViolationInterface;

/**
 * TODO: This normalizer should be reworked and splitted in smaller normalizers
 *       with more restrictive support constraints.
 *
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductViolationNormalizer implements NormalizerInterface, CacheableSupportsMethodInterface
{
    /** @var string[] */
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

        if ('identifier' === $propertyPath) {
            return [
                'attribute' => $this->attributeRepository->getIdentifierCode(),
                'locale'    => null,
                'scope'     => null,
                'message'   => $violation->getMessage(),
            ];
        }

        if (1 === preg_match('|^values\[(?P<attribute>[a-z0-9-_\<\>]+)|i', $propertyPath, $matches)) {
            if (!isset($context['product']) && !isset($context['productModel'])) {
                throw new \InvalidArgumentException('Expects a product or product model context');
            }

            $attribute = explode('-', $matches['attribute']);
            $attributeCode = $attribute[0];

            // TODO: TIP-722 - to revert once the identifier product value is dropped.
            if ($attributeCode === $this->attributeRepository->getIdentifierCode() &&
                $violation->getConstraint() instanceof UniqueValue
            ) {
                return [];
            }

            $channel = $attribute[1] ?? null;
            $locale = $attribute[2] ?? null;

            return [
                'attribute' => $attributeCode,
                'locale'    => '<all_locales>' === $locale ? null : $locale,
                'scope'     => '<all_channels>' === $channel ? null : $channel,
                'message'   => $violation->getMessage(),
            ];
        }

        if (0 === strpos($propertyPath, 'values[')) {
            if (!isset($context['product']) && !isset($context['productModel'])) {
                throw new \InvalidArgumentException('Expects a product or product model context');
            }

            $codeStart = strpos($propertyPath, '[') + 1;
            $codeLength = strpos($propertyPath, ']') - $codeStart;
            $attribute = json_decode(substr($propertyPath, $codeStart, $codeLength), true);

            return [
                'attribute' => $attribute['code'],
                'locale'    => $attribute['locale'],
                'scope'     => $attribute['scope'],
                'message'   => $violation->getMessage(),
            ];
        }

        return [
            'global'  => true,
            'message' => $violation->getMessage(),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return in_array($format, $this->supportedFormats) && $data instanceof ConstraintViolationInterface;
    }

    public function hasCacheableSupportsMethod(): bool
    {
        return true;
    }
}
