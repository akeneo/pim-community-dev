<?php
declare(strict_types=1);

namespace Pim\Component\Catalog\Normalizer\Standard;

use Pim\Component\Catalog\Model\ProductModelInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Normalizer\SerializerAwareNormalizer;

/**
 * @author    Arnaud Langlade <arnaud.langlade@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductModelNormalizer extends SerializerAwareNormalizer implements NormalizerInterface
{
    private const FIELD_CODE = 'code';
    private const FIELD_FAMILY_VARIANT = 'family_variant';
    private const FIELD_CATEGORIES = 'categories';
    private const FIELD_VALUES = 'values';
    private const FIELD_CREATED = 'created';
    private const FIELD_UPDATED = 'updated';

    /**
     * {@inheritdoc}
     *
     * @param ProductModelInterface $productModel
     */
    public function normalize($productModel, $format = null, array $context = array()): array
    {
        if (!$this->serializer instanceof NormalizerInterface) {
            throw new \LogicException('Serializer must be a normalizer');
        }

        $data[self::FIELD_CODE] = $productModel->getCode();
        $data[self::FIELD_FAMILY_VARIANT] = $productModel->getFamilyVariant()->getCode();
        $data[self::FIELD_CATEGORIES] = $productModel->getCategoryCodes();
        $data[self::FIELD_VALUES] = $this->serializer->normalize($productModel->getValues(), $format, $context);
        $data[self::FIELD_CREATED] = $this->serializer->normalize($productModel->getCreated(), $format, $context);
        $data[self::FIELD_UPDATED] = $this->serializer->normalize($productModel->getUpdated(), $format, $context);

        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null): bool
    {
        return $data instanceof ProductModelInterface && 'standard' === $format;
    }
}
