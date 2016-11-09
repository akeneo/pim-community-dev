<?php

namespace Pim\Component\Connector\ArrayConverter\StandardToFlat;

use Pim\Component\Connector\ArrayConverter\ArrayConverterInterface;

/**
 * Convert standard format to flat format for currency
 *
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class Currency extends AbstractSimpleArrayConverter implements ArrayConverterInterface
{
    /**
     * {@inheritdoc}
     */
    protected function convertProperty($property, $data, array $convertedItem, array $options)
    {
        switch ($property) {
            case 'enabled':
                $convertedItem['activated'] = (true === $data) ? '1' : '0';
                break;
            default:
                $convertedItem[$property] = (string) $data;
        }

        return $convertedItem;
    }
}
