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
    /** @var IdentifiableObjectRepositoryInterface */
    private $localeRepository;

    public function __construct(IdentifiableObjectRepositoryInterface $localeRepository)
    {
        $this->localeRepository = $localeRepository;
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
    public function supportsNormalization($data, $format = null)
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
            if (empty($locales) || in_array($translation->getLocale(), $locales)) {
                $locale = $this->localeRepository->findOneByIdentifier($translation->getLocale());
                if (null === $locale || !$locale->isActivated()) {
                    continue;
                }

                $labels[$translation->getLocale()] = $translation->getValue();
            }
        }

        return $labels;
    }
}
