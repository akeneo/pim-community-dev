<?php

namespace Pim\Component\Connector\ArrayConverter\Flat;

use Pim\Component\Catalog\Repository\LocaleRepositoryInterface;
use Pim\Component\Connector\ArrayConverter\FieldsRequirementValidator;
use Pim\Component\Connector\ArrayConverter\StandardArrayConverterInterface;
use Pim\Component\Connector\Exception\ArrayConversionException;

/**
 * Convert flat format to standard format for attribute option
 *
 * @author    Nicolas Dupont <nicola@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AttributeOptionStandardConverter implements StandardArrayConverterInterface
{
    /** @var LocaleRepositoryInterface */
    protected $localeRepository;

    /** @var FieldsRequirementValidator */
    protected $validator;

    /**
     * @param LocaleRepositoryInterface  $localeRepository
     * @param FieldsRequirementValidator $validator
     */
    public function __construct(
        LocaleRepositoryInterface $localeRepository,
        FieldsRequirementValidator $validator
    ) {
        $this->localeRepository = $localeRepository;
        $this->validator        = $validator;
    }

    /**
     * {@inheritdoc}
     *
     * Converts flat csv array to standard structured array:
     *
     * Before:
     * {
     *     'attribute': 'maximum_print_size',
     *     'code': '210_x_1219_mm',
     *     'sort_order': '2',
     *     'label-de_DE': '210 x 1219 mm',
     *     'label-en_US': '210 x 1219 mm',
     *     'label-fr_FR': '210 x 1219 mm'
     * }
     *
     * After:
     * {
     *     'attribute': 'maximum_print_size',
     *     'code': '210_x_1219_mm',
     *     'sort_order': 2,
     *     'labels': {
     *         'de_DE': '210 x 1219 mm',
     *         'en_US': '210 x 1219 mm',
     *         'fr_FR': '210 x 1219 mm'
     *     }
     * }
     */
    public function convert(array $item, array $options = [])
    {
        $this->validate($item);
        $convertedItem = ['labels' => []];
        foreach ($item as $field => $data) {
            $isLabel = false !== strpos($field, 'label-', 0);
            if ($isLabel) {
                $labelTokens = explode('-', $field);
                $labelLocale = $labelTokens[1];
                $convertedItem['labels'][$labelLocale] = $data;
            } else {
                $convertedItem[$field] = $data;
            }
        }
        if (!isset($convertedItem['sort_order'])) {
            $convertedItem['sort_order'] = 1;
        }
        $convertedItem['sort_order'] = (int) $convertedItem['sort_order'];

        return $convertedItem;
    }

    /**
     * @param array $item
     *
     * @throws ArrayConversionException
     */
    protected function validate(array $item)
    {
        $requiredFields = ['attribute', 'code'];

        $this->validator->validateFields($item, $requiredFields, false);

        $authorizedFields = array_merge($requiredFields, ['sort_order']);
        $localeCodes = $this->localeRepository->getActivatedLocaleCodes();
        foreach ($localeCodes as $code) {
            $authorizedFields[] = 'label-' . $code;
        }

        foreach ($item as $field => $data) {
            if (!in_array($field, $authorizedFields)) {
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
}
