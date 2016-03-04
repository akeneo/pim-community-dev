<?php

namespace Pim\Component\Connector\ArrayConverter\Flat;

use Pim\Component\Connector\ArrayConverter\FieldsRequirementValidator;
use Pim\Component\Connector\ArrayConverter\StandardArrayConverterInterface;

/**
 * Group Type Flat to Standard format Converter
 *
 * @author    Pierre Allard <pierre.allard@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GroupTypeStandardConverter implements StandardArrayConverterInterface
{
    /** @var FieldsRequirementValidator */
    protected $validator;

    /**
     * @param FieldsRequirementValidator $validator
     */
    public function __construct(FieldsRequirementValidator $validator)
    {
        $this->validator = $validator;
    }

    /**
     * {@inheritdoc}
     *
     * Converts flat csv array to standard structured array:
     *
     * Before:
     * [
     *     'code'        => 'VARIANT',
     *     'is_variant'  => 1,
     *     'label-en_US' => 'variant',
     *     'label-fr_FR' => 'variantes',
     * ]
     *
     * After:
     * [
     *     'code'        => 'VARIANT',
     *     'is_variant'  => true,
     *     'label'       => [
     *         'en_US' => 'variant',
     *         'fr_FR' => 'variantes',
     *     ]
     * ]
     */
    public function convert(array $item, array $options = [])
    {
        $this->validator->validateFields($item, ['code']);

        $convertedItem = [];
        foreach ($item as $field => $data) {
            $convertedItem = $this->convertField($convertedItem, $field, $data);
        }

        return $convertedItem;
    }

    /**
     * @param array  $convertedItem
     * @param string $field
     * @param mixed  $data
     *
     * @return array
     */
    protected function convertField(array $convertedItem, $field, $data)
    {
        if ('code' === $field) {
            $convertedItem[$field] = $data;
        } elseif ('is_variant' === $field) {
            $convertedItem['is_variant'] = (bool) $data;
        } elseif (preg_match('/^label-(?P<locale>[\w_]+)$/', $field, $matches)) {
            if (!isset($convertedItem['label'])) {
                $convertedItem['label'] = [];
            }
            $convertedItem['label'][$matches['locale']] = $data;
        }

        return $convertedItem;
    }
}
