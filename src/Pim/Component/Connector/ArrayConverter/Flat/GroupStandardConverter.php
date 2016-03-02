<?php

namespace Pim\Component\Connector\ArrayConverter\Flat;

use Pim\Component\Catalog\Repository\LocaleRepositoryInterface;
use Pim\Component\Connector\ArrayConverter\FieldsRequirementValidator;
use Pim\Component\Connector\ArrayConverter\StandardArrayConverterInterface;
use Pim\Component\Connector\Exception\ArrayConversionException;

/**
 * Group Flat Converter
 *
 * @author    Olivier Soulet <olivier.soulet@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GroupStandardConverter implements StandardArrayConverterInterface
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
     * Convert flat array to structured array:
     *
     * Before:
     * [
     *      'code' => 'group1',
     *      'type' => 'RELATED',
     *      'label-de_DE' => '',
     *      'label-en_US' => '',
     *      'label-fr_FR' => '',
     * ]
     *
     * After:
     * {
     *     "code": "mycode",
     *     "type": "RELATED",
     *     "labels": {
     *         "en_US": "T-shirt very beautiful",
     *         "fr_FR": "T-shirt super beau",
     *     },
     * }
     */
    public function convert(array $item, array $options = [])
    {
        $this->validate($item);
        $convertedItem = ['labels' => []];
        foreach ($item as $field => $data) {
            if ('' !== $data) {
                $convertedItem = $this->convertField($convertedItem, $field, $data);
            }
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
        if (false !== strpos($field, 'label-', 0)) {
            $labelTokens = explode('-', $field);
            $labelLocale = $labelTokens[1];
            $convertedItem['labels'][$labelLocale] = $data;
        } else {
            switch ($field) {
                case 'code':
                case 'type':
                    $convertedItem[$field] = $data;
                    break;
            }
        }

        return $convertedItem;
    }

    /**
     * @param array $item
     */
    protected function validate(array $item)
    {
        $this->validator->validateFields($item, ['code', 'type']);
        $this->validateAuthorizedFields($item, ['type', 'code']);
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
