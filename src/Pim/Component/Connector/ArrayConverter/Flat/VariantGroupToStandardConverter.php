<?php

namespace Pim\Component\Connector\ArrayConverter\Flat;

use Pim\Bundle\CatalogBundle\Repository\AttributeRepositoryInterface;
use Pim\Bundle\CatalogBundle\Repository\LocaleRepositoryInterface;
use Pim\Component\Connector\ArrayConverter\StandardArrayConverterInterface;
use Pim\Component\Connector\Exception\ArrayConversionException;

/**
 * Variant group Flat Converter
 *
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class VariantGroupToStandardConverter implements StandardArrayConverterInterface
{
    /** @var LocaleRepositoryInterface */
    protected $localeRepository;

    /** @var LocaleRepositoryInterface */
    protected $attributeRepository;

    /** @var ProductToStandardConverter */
    protected $productConverter;

    /**
     * @param LocaleRepositoryInterface    $localeRepository
     * @param AttributeRepositoryInterface $attributeRepository
     * @param ProductToStandardConverter   $productConverter
     */
    public function __construct(
        LocaleRepositoryInterface $localeRepository,
        AttributeRepositoryInterface $attributeRepository,
        ProductToStandardConverter $productConverter
    ) {
        $this->localeRepository    = $localeRepository;
        $this->attributeRepository = $attributeRepository;
        $this->productConverter    = $productConverter;
    }

    /**
     * Convert flat array to structured array:
     *
     * Before:
     * [
     *     'code': 'mycode',
     *     'axis': 'main_color,'secondary_color'
     *     'label-fr_FR': 'T-shirt super beau',
     *     'label-en_US': 'T-shirt very beautiful',
     *     'type': 'VARIANT',
     *     'main_color': 'white',
     *     'tshirt_style': 'turtleneck,sportwear',
     *     'description-fr_FR-ecommerce': '<p>description</p>'
     *     'description-en_US-ecommerce': '<p>description</p>'
     * ]
     *
     * After:
     * {
     *     "code": "mycode",
     *     "labels": {
     *         "en_US": "T-shirt very beautiful",
     *         "fr_FR": "T-shirt super beau"
     *     }
     *     "axis": ["main_color", "secondary_color"],
     *     "type": "VARIANT",
     *     "values": {
     *         "main_color": "white",
     *         "tshirt_style": ["turtleneck","sportwear"],
     *         "description": [
     *              {
     *                  "locale": "fr_FR",
     *                  "scope": "ecommerce",
     *                  "data": "<p>description</p>",
     *              },
     *              {
     *                  "locale": "en_US",
     *                  "scope": "ecommerce",
     *                  "data": "<p>description</p>",
     *              }
     *          ]
     *     }
     * }
     *
     * @param array $item Representing a flat variant group
     *
     * @return array structured $item
     */
    public function convert(array $item)
    {
        $this->validate($item);
        $convertedItem = ['labels' => []];
        foreach ($item as $field => $data) {
            if ('' !== $data) {
                $convertedItem = $this->convertField($convertedItem, $field, $data);
            }
        }

        if (isset($convertedItem['values'])) {
            $convertedItem['values'] = $this->productConverter->convert($convertedItem['values']);
            unset($convertedItem['values']['enabled']);
        }

        if (!isset($convertedItem['type'])) {
            $convertedItem['type'] = 'VARIANT';
        }

        return $convertedItem;
    }

    /**
     * @param string $field
     * @param mixed  $data
     *
     * @return array
     */
    protected function convertField($convertedItem, $field, $data)
    {
        switch ($field) {
            case false !== strpos($field, 'label-', 0):
                $labelTokens = explode('-', $field);
                $labelLocale = $labelTokens[1];
                $convertedItem['labels'][$labelLocale] = $data;
                break;

            case 'code':
            case 'type':
                $convertedItem[$field] = $data;
                break;

            case 'axis':
                $convertedItem[$field] = explode(',', $data);
                break;

            default:
                $convertedItem['values'][$field] = $data;
        }

        return $convertedItem;
    }

    /**
     * @param array $item
     */
    protected function validate(array $item)
    {
        $this->validateRequiredFields($item, ['code']);
        $this->validateAuthorizedFields($item, ['axis', 'type', 'code']);
    }

    /**
     * @param array $item
     * @param array $requiredFields
     *
     * @throws ArrayConversionException
     */
    protected function validateRequiredFields(array $item, array $requiredFields)
    {
        foreach ($requiredFields as $requiredField) {
            if (!in_array($requiredField, array_keys($item))) {
                throw new ArrayConversionException(
                    sprintf(
                        'Field "%s" is expected, provided fields are "%s"',
                        $requiredField,
                        implode(', ', array_keys($item))
                    )
                );
            }

            if ('' === $item[$requiredField]) {
                throw new ArrayConversionException(
                    sprintf(
                        'Field "%s" must be filled',
                        $requiredField,
                        implode(', ', array_keys($item))
                    )
                );
            }
        }
    }

    /**
     * @param array $item
     * @param array $authorizedFields
     *
     * @throws ArrayConversionException
     */
    protected function validateAuthorizedFields(array $item, array $authorizedFields)
    {
        $localeCodes = $this->localeRepository->getActivatedLocaleCodes();
        foreach ($localeCodes as $code) {
            $authorizedFields[] = 'label-' . $code;
        }

        foreach ($item as $field => $data) {
            if (!in_array($field, $authorizedFields) && !$this->isAttribute($field)) {
                throw new ArrayConversionException(
                    sprintf(
                        'Field "%s" is provided, authorized fields are: "%s"',
                        $field,
                        implode(', ', $authorizedFields)
                    )
                );
            }
        }
    }

    /**
     * @param string $code
     *
     * @return bool
     */
    protected function isAttribute($code)
    {
        return null !== $this->attributeRepository->getIdentifierCode($code);
    }
}
