<?php

namespace Akeneo\Pim\Structure\Component\Normalizer\Standard;

use Akeneo\Pim\Structure\Component\Model\AttributeOptionInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Symfony\Component\Serializer\Normalizer\CacheableSupportsMethodInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AttributeOptionNormalizer implements NormalizerInterface, CacheableSupportsMethodInterface
{
    public function __construct(private readonly IdentifiableObjectRepositoryInterface $localeRepository)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($attributeOption, $format = null, array $context = [])
    {
        return [
            'code'       => $attributeOption->getCode(),
            'attribute'  => null === $attributeOption->getAttribute() ?
                null : $attributeOption->getAttribute()->getCode(),
            'sort_order' => (int) $attributeOption->getSortOrder(),
            'labels'     => $this->normalizeLabels($attributeOption, $context),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null): bool
    {
        return $data instanceof AttributeOptionInterface && 'standard' === $format;
    }

    public function hasCacheableSupportsMethod(): bool
    {
        return true;
    }

    /**
     * Returns an array containing the label values
     *
     * @param AttributeOptionInterface $attributeOption
     * @param array                    $context
     *
     * @return array
     */
    protected function normalizeLabels(AttributeOptionInterface $attributeOption, $context)
    {
        $locales = isset($context['locales']) ? $context['locales'] : [];
        $labels = array_fill_keys($locales, null);

        foreach ($attributeOption->getOptionValues() as $translation) {
            $locale = $this->localeRepository->findOneByIdentifier($translation->getLocale());
            if (null === $locale || !$locale->isActivated() || null === $translation->getValue()) {
                continue;
            }
            if (empty($locales) || in_array($locale->getCode(), $locales)) {
                $labels[$locale->getCode()] = $translation->getValue();
            }
        }

        return $labels;
    }
}
