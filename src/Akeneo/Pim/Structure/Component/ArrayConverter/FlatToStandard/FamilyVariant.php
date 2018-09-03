<?php

namespace Akeneo\Pim\Structure\Component\ArrayConverter\FlatToStandard;

use Akeneo\Tool\Component\Connector\ArrayConverter\ArrayConverterInterface;
use Akeneo\Tool\Component\Connector\ArrayConverter\FieldsRequirementChecker;

/**
 * @author    Damien Carcel <damien.carcel@gmail.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FamilyVariant implements ArrayConverterInterface
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
     *      'code'                 => 'my-tshirt',
     *      'family'               => 't-shirt',
     *      'label-fr_FR'          => 'Mon tshirt',
     *      'label-en_US'          => 'My tshirt',
     *      'variant-axes_1'       => 'color',
     *      'variant-attributes_1' => 'description',
     *      'variant-axes_2'       => 'size,other',
     *      'variant-attributes_2' => 'size,other,sku',
     * ]
     *
     * After:
     * [
     *      'labels' => [
     *          'fr_FR' => 'Mon tshirt',
     *          'en_US' => 'My tshirt',
     *      ],
     *      'variant_attribute_sets' => [
     *          [
     *              'level' => 1,
     *              'axes' => ['color'],
     *              'attributes' => ['description'],
     *          ],
     *          [
     *              'level' => 2,
     *              'axes' => ['size', 'other'],
     *              'attributes' => ['size', 'other', 'sku'],
     *          ],
     *      ],
     *      'code' => 'my-tshirt',
     *      'family' => 't-shirt',
     * ]
     */
    public function convert(array $item, array $options = []): array
    {
        $this->fieldChecker->checkFieldsPresence($item, ['code']);
        $this->fieldChecker->checkFieldsPresence($item, ['family']);
        $this->fieldChecker->checkFieldsPresence($item, ['variant-axes_1']);
        $this->fieldChecker->checkFieldsFilling($item, ['code']);
        $this->fieldChecker->checkFieldsFilling($item, ['family']);
        $this->fieldChecker->checkFieldsFilling($item, ['variant-axes_1']);

        $convertedItem = ['labels' => [], 'variant_attribute_sets' => []];
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
    protected function convertField(array $convertedItem, string $field, $data): array
    {
        if (false !== strpos($field, 'label-', 0)) {
            $labelTokens = explode('-', $field);
            $labelLocale = $labelTokens[1];
            $convertedItem['labels'][$labelLocale] = $data;
        } elseif ('' !== $data) {
            switch ($field) {
                case 'code':
                case 'family':
                    $convertedItem[$field] = (string) $data;

                    break;
                case (false !== strpos($field, 'variant-axes_')):
                    $matches = null;
                    preg_match('/^variant-axes_(?P<level>.*)$/', $field, $matches);
                    $level = (int) $matches['level'];

                    if (!isset($convertedItem['variant_attribute_sets'][$level - 1]) ||
                        !isset($convertedItem['variant_attribute_sets'][$level - 1]['level'])
                    ) {
                        $convertedItem['variant_attribute_sets'][$level - 1]['level'] = $level;
                    }

                    $convertedItem['variant_attribute_sets'][$level - 1]['axes'] = explode(',', $data);

                    break;
                case (false !== strpos($field, 'variant-attributes_')):
                    $matches = null;
                    preg_match('/^variant-attributes_(?P<level>.*)$/', $field, $matches);
                    $level = (int) $matches['level'];

                    if (!isset($convertedItem['variant_attribute_sets'][$level - 1]) ||
                        !isset($convertedItem['variant_attribute_sets'][$level - 1]['level'])
                    ) {
                        $convertedItem['variant_attribute_sets'][$level - 1]['level'] = $level;
                    }

                    $convertedItem['variant_attribute_sets'][$level - 1]['attributes'] = explode(
                        ',',
                        $data
                    );

                    break;
            }
        }

        return $convertedItem;
    }
}
