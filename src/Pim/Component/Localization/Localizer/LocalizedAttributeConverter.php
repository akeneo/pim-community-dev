<?php

namespace Pim\Component\Localization\Localizer;

use Pim\Bundle\CatalogBundle\Repository\AttributeRepositoryInterface;

/**
 * Convert localized attributes to default format
 *
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class LocalizedAttributeConverter implements LocalizedAttributeConverterInterface
{
    /** @var LocalizerRegistryInterface */
    protected $localizerRegistry;

    /** @var AttributeRepositoryInterface */
    protected $attributeRepository;

    /**
     * @param LocalizerRegistryInterface   $localizerRegistry
     * @param AttributeRepositoryInterface $attributeRepository
     */
    public function __construct(
        LocalizerRegistryInterface $localizerRegistry,
        AttributeRepositoryInterface $attributeRepository
    ) {
        $this->localizerRegistry   = $localizerRegistry;
        $this->attributeRepository = $attributeRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function convertLocalizedToDefaultValues(array $items, array $options = [])
    {
        $attributeTypes = $this->attributeRepository->getAttributeTypeByCodes(array_keys($items));

        foreach ($items as $code => $item) {
            if (isset($attributeTypes[$code])) {
                $localizer = $this->localizerRegistry->getLocalizer($attributeTypes[$code]);

                if (null !== $localizer) {
                    foreach ($item as $i => $data) {
                        $items[$code][$i] = $this->convertLocalizedToDefaultValue($localizer, $data, $options, $code);
                    }
                }
            }
        }

        return $items;
    }

    /**
     * {@inheritdoc}
     */
    public function convertDefaultToLocalizedValue($code, $data, $options = [])
    {
        $attribute = $this->attributeRepository->findOneBy(['code' => $code]);
        if (null === $attribute) {
            return $data;
        }

        $attributeType = $attribute->getAttributeType();
        if (null === $attributeType) {
            return $data;
        }

        $localizer = $this->localizerRegistry->getLocalizer($attributeType);
        if (null === $localizer) {
            return $data;
        }

        return $localizer->convertDefaultToLocalized($data, $options);
    }

    /**
     * Convert a localized attribute
     *
     * @param LocalizerInterface $localizer
     * @param array              $item
     * @param array              $options
     * @param string             $code
     *
     * @throws \LogicException
     *
     * @return array
     */
    protected function convertLocalizedToDefaultValue(LocalizerInterface $localizer, array $item, array $options, $code)
    {
        if ($localizer->isValid($item['data'], $options, $code)) {
            $item['data'] = $localizer->delocalize($item['data'], $options);

            return $item;
        }
    }
}
