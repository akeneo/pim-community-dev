<?php

namespace Pim\Component\Connector\ArrayConverter\Flat;

use Pim\Component\Connector\ArrayConverter\FieldsRequirementValidator;
use Pim\Component\Connector\ArrayConverter\StandardArrayConverterInterface;

/**
 * Category Flat Converter
 *
 * @author    Olivier Soulet <olivier.soulet@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CategoryStandardConverter implements StandardArrayConverterInterface
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
     *      'code'        => 'mycode',
     *      'parent'      => 'master',
     *      'label-fr_FR' => 'T-shirt super beau',
     *      'label-en_US' => 'T-shirt very beautiful',
     * ]
     *
     * After:
     * [
     *      'code'   => 'mycode',
     *      'parent' => 'master',
     *      'labels' => [
     *          'fr_FR' => 'T-shirt super beau',
     *          'en_US' => 'T-shirt very beautiful',
     *      ],
     * ]
     */
    public function convert(array $item, array $options = [])
    {
        $this->validator->validateFields($item, ['code']);

        $convertedItem = ['labels' => []];
        foreach ($item as $field => $data) {
            if ('' !== $data) {
                $convertedItem = $this->convertField($convertedItem, $field, $data);
            }
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
    protected function convertField($convertedItem, $field, $data)
    {
        if (false !== strpos($field, 'label-', 0)) {
            $labelTokens = explode('-', $field);
            $labelLocale = $labelTokens[1];
            $convertedItem['labels'][$labelLocale] = $data;
        } else {
            switch ($field) {
                case 'code':
                case 'parent':
                    $convertedItem[$field] = (string) $data;
                    break;
            }
        }

        return $convertedItem;
    }
}
