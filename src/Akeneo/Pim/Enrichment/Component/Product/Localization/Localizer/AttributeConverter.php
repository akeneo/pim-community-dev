<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Localization\Localizer;

use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;
use Akeneo\Tool\Component\Localization\Localizer\LocalizerInterface;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\ConstraintViolationListInterface;

/**
 * Convert localized attributes to default format
 *
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AttributeConverter implements AttributeConverterInterface
{
    /** @var LocalizerRegistryInterface */
    protected $localizerRegistry;

    /** @var AttributeRepositoryInterface */
    protected $attributeRepository;

    /** @var ConstraintViolationListInterface */
    protected $violations;

    /** @var array[] */
    protected $attributeTypeByCodes;

    /**
     * @param LocalizerRegistryInterface   $localizerRegistry
     * @param AttributeRepositoryInterface $attributeRepository
     */
    public function __construct(
        LocalizerRegistryInterface $localizerRegistry,
        AttributeRepositoryInterface $attributeRepository
    ) {
        $this->localizerRegistry = $localizerRegistry;
        $this->attributeRepository = $attributeRepository;
        $this->attributeTypeByCodes = [];
    }

    /**
     * {@inheritdoc}
     */
    public function convertToDefaultFormats(array $items, array $options = [])
    {
        $this->violations = new ConstraintViolationList();
        $this->cacheAttributeTypeByCodes(array_keys($items));

        foreach ($items as $code => $item) {
            if (isset($this->attributeTypeByCodes[$code])) {
                $localizer = $this->localizerRegistry->getLocalizer($this->attributeTypeByCodes[$code]);

                if (null !== $localizer) {
                    foreach ($item as $index => $data) {
                        $items[$code][$index] = $this->convertToDefaultFormat(
                            $localizer,
                            $data,
                            $options,
                            $this->buildPropertyPath($data, $code)
                        );
                    }
                }
            }
        }

        return $items;
    }

    /**
     * {@inheritdoc}
     */
    public function getViolations()
    {
        return $this->violations;
    }

    /**
     * {@inheritdoc}
     *
     * Before:
     * [
     *     "name": [{
     *         "locale": "fr_FR",
     *         "scope":  null,
     *         "data":  "T-shirt super beau",
     *     }],
     *     "price": [
     *          {
     *              "locale": null,
     *              "scope":  ecommerce,
     *              "data":   [
     *                  {"data": 10.78, "currency": "EUR"},
     *                  {"data": 24, "currency": "USD"},
     *                  {"data": 20.75, "currency": "CHF"}
     *              ]
     *          }
     *     ],
     *     "length": [{
     *         "locale": "en_US",
     *         "scope":  "mobile",
     *         "data":   {"data": 10.45, "unit": "CENTIMETER"}
     *     }]
     *     [...]
     *
     * After:
     * [
     *     "name": [{
     *         "locale": "fr_FR",
     *         "scope":  null,
     *         "data":  "T-shirt super beau",
     *     }],
     *     "price": [
     *          {
     *              "locale": null,
     *              "scope":  ecommerce,
     *              "data":   [
     *                  {"data": "10,78", "currency": "EUR"},
     *                  {"data": "24", "currency": "USD"},
     *                  {"data": "20,75", "currency": "CHF"}
     *              ]
     *          }
     *     ],
     *     "length": [{
     *         "locale": "en_US",
     *         "scope":  "mobile",
     *         "data":   {"data": "10,45", "unit": "CENTIMETER"}
     *     }]
     *     [...]
     */
    public function convertToLocalizedFormats(array $items, array $options = [])
    {
        $this->cacheAttributeTypeByCodes(array_keys($items));

        foreach ($items as $code => $item) {
            if (isset($this->attributeTypeByCodes[$code])) {
                $localizer = $this->localizerRegistry->getLocalizer($this->attributeTypeByCodes[$code]);

                if (null !== $localizer) {
                    foreach ($item as $index => $data) {
                        $items[$code][$index]['data'] = $localizer->localize($data['data'], $options);
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
     * @param string             $path
     *
     * @return array
     */
    protected function convertToDefaultFormat(LocalizerInterface $localizer, array $item, array $options, $path)
    {
        $violations = $localizer->validate($item['data'], $path, $options);
        if (null !== $violations && $violations->count() > 0) {
            $this->violations->addAll($violations);
        }

        $item['data'] = $localizer->delocalize($item['data'], $options);

        return $item;
    }

    /**
     * Build the property path of the attribute
     *
     * @param array  $data
     * @param string $code
     *
     * @return string
     */
    protected function buildPropertyPath(array $data, $code)
    {
        $path = $code;

        if (isset($data['scope']) && '' !== $data['scope']) {
            $path .= sprintf('-%s', $data['scope']);
        }

        if (isset($data['locale']) && '' !== $data['locale']) {
            $path .= sprintf('-%s', $data['locale']);
        }

        return sprintf('values[%s]', $path);
    }

    /**
     * @param array $codes
     */
    private function cacheAttributeTypeByCodes(array $codes)
    {
        $codesToFetch = array_diff($codes, array_keys($this->attributeTypeByCodes));

        // we can have numeric keys here, we can't use array_merge :(
        $this->attributeTypeByCodes += $this->attributeRepository->getAttributeTypeByCodes($codesToFetch);
    }
}
