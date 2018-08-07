<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Connector\ArrayConverter\StandardToFlat;

use Akeneo\Pim\Enrichment\Component\Product\Connector\ArrayConverter\StandardToFlat\Product\ProductValueConverter;
use Akeneo\Tool\Component\Connector\ArrayConverter\ArrayConverterInterface;
use Akeneo\Tool\Component\Connector\ArrayConverter\StandardToFlat\AbstractSimpleArrayConverter;

/**
 * Convert standard format to flat format for product
 *
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class Product extends AbstractSimpleArrayConverter implements ArrayConverterInterface
{
    /** @var ProductValueConverter */
    protected $valueConverter;

    /**
     * @param ProductValueConverter $valueConverter
     */
    public function __construct(ProductValueConverter $valueConverter)
    {
        $this->valueConverter = $valueConverter;
    }

    /**
     * {@inheritdoc}
     */
    protected function convertProperty($property, $data, array $convertedItem, array $options)
    {
        switch ($property) {
            case 'associations':
                $convertedItem = $this->convertAssociations($data, $convertedItem);
                break;
            case 'categories':
                $convertedItem[$property] = implode(',', $data);
                break;
            case 'enabled':
                $convertedItem[$property] = false === $data || null === $data ? '0' : '1';
                break;
            case 'family':
                $convertedItem[$property] = (string) $data;
                break;
            case 'parent':
                if (null !== $data && '' !== $data) {
                    $convertedItem[$property] = (string) $data;
                }
                break;
            case 'groups':
                $convertedItem = $this->convertGroups($data, $convertedItem);
                break;
            case 'values':
                foreach ($data as $code => $attribute) {
                    $convertedItem = $convertedItem + $this->valueConverter->convertAttribute($code, $attribute);
                }
                break;
            case 'identifier':
            case 'created':
            case 'updated':
                break;
            default:
                $convertedItem = $convertedItem + $this->valueConverter->convertAttribute($property, $data);
        }

        return $convertedItem;
    }

    /**
     * Convert flat groups to flat unified groups.
     *
     * @param mixed $data
     * @param array $convertedItem
     *
     * @return array
     */
    protected function convertGroups($data, array $convertedItem)
    {
        if (!array_key_exists('groups', $convertedItem)) {
            $convertedItem['groups'] = '';
        }

        $groups = is_array($data) ? implode(',', $data) : (string) $data;
        if ('' !== $groups) {
            if ('' !== $convertedItem['groups']) {
                $convertedItem['groups'] .= sprintf(',%s', $groups);
            } else {
                $convertedItem['groups'] = $groups;
            }
        }

        return $convertedItem;
    }

    /**
     * Convert flat formatted associations to standard ones.
     *
     * Given this $data:
     * [
     *     'UPSELL' => [
     *         'groups'   => [],
     *         'products' => []
     *     ],
     *     'X_SELL' => [
     *         'groups'   => ['akeneo_tshirt', 'oro_tshirt'],
     *         'products' => ['akn_ts', 'oro_tsh']
     *     ]
     * ]
     *
     * It will return:
     * [
     *     'UPSELL-groups'   => '',
     *     'UPSELL-products' => '',
     *     'X_SELL-groups'   => 'akeneo_tshirt,oro_tshirt',
     *     'X_SELL-products' => 'akn_ts,oro_tsh',
     * ]
     *
     * @param array $data
     * @param array $convertedItem
     *
     * @return array
     */
    protected function convertAssociations(array $data, array $convertedItem)
    {
        foreach ($data as $assocName => $associations) {
            foreach ($associations as $assocType => $entities) {
                $propertyName = sprintf('%s-%s', $assocName, $assocType);
                $convertedItem[$propertyName] = implode(',', $entities);
            }
        }

        return $convertedItem;
    }
}
