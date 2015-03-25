<?php

namespace Pim\Bundle\BaseConnectorBundle\Processor\Denormalization\ArrayConverter\Structured;

use Pim\Bundle\BaseConnectorBundle\Processor\Denormalization\ArrayConverter\StandardArrayConverterInterface;

/**
 * Attribute Option Structured Converter
 *
 * @author    Nicolas Dupont <nicola@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AttributeOptionToStandardConverter implements StandardArrayConverterInterface
{
    /**
     * {@inheritdoc}
     *
     * Converts yaml array to standard structured array:
     *
     * Before:
     * {
     *     'attribute': 'maximum_print_size',
     *     'code': '210_x_1219_mm',
     *     'sortOrder': 2,
     *     'labels': {
     *         'de_DE': '210 x 1219 mm',
     *         'en_US': '210 x 1219 mm',
     *         'fr_FR': '210 x 1219 mm'
     *     }
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
        // TODO: option resolver!
        $item['sort_order'] = $item['sortOrder'];
        unset($item['sortOrder']);

        return $item;
    }
}
