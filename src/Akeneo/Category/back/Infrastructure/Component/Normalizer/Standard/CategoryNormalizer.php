<?php

namespace Akeneo\Category\Infrastructure\Component\Normalizer\Standard;

use Akeneo\Category\Domain\Model\Attribute\Attribute;
use Akeneo\Category\Domain\Model\Category;
use Akeneo\Category\Domain\ValueObject\ValueCollection;
use Akeneo\Category\Infrastructure\Component\Classification\Model\CategoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Normalizer\Standard\DateTimeNormalizer;
use Akeneo\Pim\Enrichment\Component\Product\Normalizer\Standard\TranslationNormalizer;
use Symfony\Component\Serializer\Normalizer\CacheableSupportsMethodInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CategoryNormalizer implements NormalizerInterface, CacheableSupportsMethodInterface
{
    /** @var TranslationNormalizer */
    protected $translationNormalizer;

    private DateTimeNormalizer $dateTimeNormalizer;

    /**
     * @param TranslationNormalizer $translationNormalizer
     */
    public function __construct(TranslationNormalizer $translationNormalizer, DateTimeNormalizer $dateTimeNormalizer)
    {
        $this->translationNormalizer = $translationNormalizer;
        $this->dateTimeNormalizer = $dateTimeNormalizer;
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($category, $format = null, array $context = [])
    {
        return [
            'code' => $category->getCode(),
            'parent' => null !== $category->getParent() ? $category->getParent()->getCode() : null,
            'updated' => $this->dateTimeNormalizer->normalize($category->getUpdated(), $format),
            'labels' => $this->translationNormalizer->normalize($category, 'standard', $context),
        ];
    }

    /**
     * @return array<string, array{
     *     identifier: string,
     *     labels: array<string, string>,
     *     type: string,
     *     order: int,
     *     data: array<string, mixed>|null
     * }>
     */
    public function normalizeWithAttributes(Category $category): array
    {
        $attributes = [];

        $templateAttributes = $category->getTemplate()->getAttributeCollection()->getAttributes();
        foreach ($templateAttributes as $attribute) {
            $code = (string)$attribute->getCode();
            $attributes[$code]['identifier'] = $attribute->getIdentifier();
            $attributes[$code]['labels'] = $attribute->getLabelCollection()->normalize();
            $attributes[$code]['type'] = (string)$attribute->getType();
            $attributes[$code]['order'] = (int)$attribute->getOrder();

            $attributeValues = $category->getAttributes();
            if ($attributeValues) {
                foreach ($category->getAttributes() as $attributeValue) {
                    if ($code === $attributeValue['attribute_code']) {
                        $localeCode = $attributeValue['locale'];

                        if ($attribute->isLocalizable() && $localeCode) {
                            $attributes[$code]['data'][$localeCode] = $attributeValues->getAttributeTextData(
                                (string)$attribute->getCode(),
                                (string)$attribute->getUuid(),
                                $localeCode
                            )['data'];
                        } else {
                            $attributes[$code]['data'] = $attributeValues->getAttributeData(
                                (string)$attribute->getCode(),
                                (string)$attribute->getUuid()
                            )['data'];
                        }
                    }
                }
            } else {
                $attributes[$code]['data'] = null;
            }
        }

        return $attributes;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof CategoryInterface && 'standard' === $format;
    }

    public function hasCacheableSupportsMethod(): bool
    {
        return true;
    }
}
