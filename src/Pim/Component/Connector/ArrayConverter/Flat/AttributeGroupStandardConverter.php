<?php

namespace Pim\Component\Connector\ArrayConverter\Flat;

use Pim\Component\Connector\ArrayConverter\FieldsRequirementChecker;
use Pim\Component\Connector\ArrayConverter\StandardArrayConverterInterface;

/**
 * Attribute Group Flat to Standard format Converter
 *
 * @author    Pierre Allard <pierre.allard@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AttributeGroupStandardConverter implements StandardArrayConverterInterface
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
     *     'code'        => 'sizes',
     *     'sort_order'  => 1,
     *     'attributes'  => 'size,main_color',
     *     'label-en_US' => 'Sizes',
     *     'label-fr_FR' => 'Tailles'
     * ]
     *
     * After:
     * [
     *     'code'       => 'sizes',
     *     'sort_order' => 1,
     *     'attributes' => ['size', 'main_color'],
     *     'label'      => [
     *         'en_US' => 'Sizes',
     *         'fr_FR' => 'Tailles'
     *     ]
     * ]
     */
    public function convert(array $item, array $options = [])
    {
        $this->fieldChecker->checkFieldsPresence($item, ['code']);
        $this->fieldChecker->checkFieldsFilling($item, ['code']);

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
        if (in_array($field, ['code', 'sort_order'])) {
            $convertedItem[$field] = $data;
        } elseif (preg_match('/^label-(?P<locale>[\w_]+)$/', $field, $matches)) {
            if (!isset($convertedItem['label'])) {
                $convertedItem['label'] = [];
            }
            $convertedItem['label'][$matches['locale']] = $data;
        } else {
            $convertedItem[$field] = empty($data) ? [] : explode(',', $data);
        }

        return $convertedItem;
    }
}
