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
    public function convert(array $items, array $options = [])
    {
        $attributeTypes = $this->attributeRepository->getAttributeTypeByCodes(array_keys($items));

        foreach ($items as $code => $item) {
            if (isset($attributeTypes[$code])) {
                $localizer = $this->localizerRegistry->getLocalizer($attributeTypes[$code]);

                if (null !== $localizer) {
                    foreach ($item as $i => $data) {
                        $items[$code][$i] = $this->convertAttribute($localizer, $data, $options, $code);
                    }
                }
            }
        }

        return $items;
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
    protected function convertAttribute(LocalizerInterface $localizer, array $item, array $options, $code)
    {
        if ($localizer->isValid($item['data'], $options)) {
            $item['data'] = $localizer->convertLocalizedToDefault($item['data']);

            return $item;
        }

        throw new \LogicException(
            sprintf(
                'Format for attribute "%s" is not respected. Format expected: [ %s ]',
                $code,
                implode(
                    ', ',
                    array_map(
                        function ($value, $option) {
                            return sprintf('"%s": "%s"', $option, $value);
                        },
                        $options,
                        array_keys($options)
                    )
                )
            )
        );
    }
}
