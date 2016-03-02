<?php

namespace Pim\Component\Connector\ArrayConverter\Flat;

use Pim\Component\Connector\ArrayConverter\FieldsRequirementValidator;
use Pim\Component\Connector\ArrayConverter\StandardArrayConverterInterface;

/**
 * Family Flat Converter
 *
 * @author    Olivier Soulet <olivier.soulet@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FamilyStandardConverter implements StandardArrayConverterInterface
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
     *      'code'                => 'pc_monitors',
     *      'label-en_US'         => 'PC Monitors',
     *      'label-fr_FR'         => 'Moniteurs',
     *      'attributes'          => 'sku,name,description,price',
     *      'attribute_as_label'  => 'name',
     *      'requirements-print'  => 'sku,name,description',
     *      'requirements-mobile' => 'sku,name',
     * ]
     *
     * After:
     * [
     *      'code'                => 'pc_monitors',
     *      'attributes'          => ['sku', 'name', 'description', 'price'],
     *      'attribute_as_label'  => 'name',
     *      'requirements' => [
     *          'mobile' => ['sku', 'name'],
     *          'print'  => ['sku', 'name', 'description'],
     *      ],
     *      'labels' => [
     *          'fr_FR' => 'Moniteurs',
     *          'en_US' => 'PC Monitors',
     *      ],
     * ]
     */
    public function convert(array $item, array $options = [])
    {
        $this->validator->validateFields($item, ['code']);

        $convertedItem = ['labels' => [], 'requirements' => []];
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
        if (false !== strpos($field, 'label-', 0)) {
            $labelTokens = explode('-', $field);
            $labelLocale = $labelTokens[1];
            $convertedItem['labels'][$labelLocale] = $data;
        } elseif ('' !== $data && false !== strpos($field, 'requirements-', 0)) {
            $requirementsTokens = explode('-', $field);
            $requirementsLocale = $requirementsTokens[1];
            $convertedItem['requirements'][$requirementsLocale] = explode(',', $data);
        } elseif ('' !== $data) {
            switch ($field) {
                case 'code':
                case 'attribute_as_label':
                    $convertedItem[$field] = (string) $data;
                    break;
                case 'attributes':
                    $convertedItem[$field] = explode(',', $data);
                    break;
            }
        }

        return $convertedItem;
    }
}
