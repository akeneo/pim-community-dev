<?php

namespace Akeneo\Pim\Structure\Component\ArrayConverter\FlatToStandard;

use Akeneo\Tool\Component\Connector\ArrayConverter\ArrayConverterInterface;
use Akeneo\Tool\Component\Connector\ArrayConverter\FieldsRequirementChecker;

/**
 * Family Flat Converter
 *
 * @author    Olivier Soulet <olivier.soulet@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Family implements ArrayConverterInterface
{
    /** @var FieldsRequirementChecker */
    protected $fieldChecker;

    /**
     * @param FieldsRequirementChecker $fieldChecker
     */
    public function __construct(FieldsRequirementChecker $fieldChecker)
    {
        $this->fieldChecker = $fieldChecker;
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
     *      'attribute_as_image'  => 'side_view',
     *      'requirements-print'  => 'sku,name,description',
     *      'requirements-mobile' => 'sku,name',
     * ]
     *
     * After:
     * [
     *      'code'                   => 'pc_monitors',
     *      'attributes'             => ['sku', 'name', 'description', 'price'],
     *      'attribute_as_label'     => 'name',
     *      'attribute_as_image'     => 'side_view',
     *      'attribute_requirements' => [
     *          'mobile' => ['sku', 'name'],
     *          'print'  => ['sku', 'name', 'description'],
     *      ],
     *      'labels'                 => [
     *          'fr_FR' => 'Moniteurs',
     *          'en_US' => 'PC Monitors',
     *      ],
     * ]
     */
    public function convert(array $item, array $options = [])
    {
        $this->fieldChecker->checkFieldsPresence($item, ['code']);
        $this->fieldChecker->checkFieldsFilling($item, ['code']);

        $convertedItem = ['labels' => [], 'attribute_requirements' => []];
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
            $convertedItem['attribute_requirements'][$requirementsLocale] = explode(',', $data);
        } elseif ('' !== $data) {
            switch ($field) {
                case 'code':
                case 'attribute_as_label':
                case 'attribute_as_image':
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
