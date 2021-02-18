<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Connector\ArrayConverter\StandardToFlat;

use Akeneo\Pim\Enrichment\Component\Product\Connector\ArrayConverter\StandardToFlat\Product\ProductValueConverter;
use Akeneo\Pim\Enrichment\Component\Product\Connector\ArrayConverter\StandardToFlat\Product\QualityScoreConverter;
use Akeneo\Pim\Enrichment\Component\Product\Connector\UseCase\GetProductsWithQualityScoresInterface;
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
            case 'quantified_associations':
                $convertedItem = $this->convertQuantifiedAssociations($data, $convertedItem);
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
            case 'quality_scores':
                if (is_array($data)) {
                    $convertedItem = $this->convertQualityScores($data, $convertedItem);
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
     * Convert standard associations to flat formatted ones.
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
     */
    protected function convertAssociations(array $data, array $convertedItem): array
    {
        foreach ($data as $assocName => $associations) {
            foreach ($associations as $assocType => $entities) {
                $propertyName = sprintf('%s-%s', $assocName, $assocType);
                $convertedItem[$propertyName] = implode(',', $entities);
            }
        }

        return $convertedItem;
    }

    /**
     * Convert standard quantified associations to flat formatted ones.
     *
     * Given this $data:
     * [
     *     'PACK' => [
     *         'products' => [],
     *         'product_models' => [],
     *     ],
     *     'PRODUCTSET' => [
     *         'products' => [
     *              [
     *                  'identifier' => 'bag',
     *                  'quantity' => 2
     *              ],
     *              [
     *                  'identifier' => 'socks',
     *                  'quantity' => 8
     *              ]
     *         ],
     *         'product_models' => [
     *              [
     *                  'identifier' => 'braided-hat',
     *                  'quantity' => 12
     *              ]
     *         ]
     *     ]
     * ]
     *
     * It will return:
     * [
     *     'PACK-products' => '',
     *     'PACK-products-quantity' => '',
     *     'PACK-product_models' => '',
     *     'PACK-product_models-quantity' => '',
     *     'PRODUCTSET-products' => 'bag,socks',
     *     'PRODUCTSET-products-quantity' => '2|8',
     *     'PRODUCTSET-product_models' => 'braided-hat',
     *     'PRODUCTSET-product_models-quantity' => '12',
     * ]
     */
    protected function convertQuantifiedAssociations(array $data, array $convertedItem): array
    {
        foreach ($data as $associationTypeCode => $quantifiedAssociations) {
            foreach ($quantifiedAssociations as $entityType => $quantifiedLinks) {
                $propertyName = sprintf('%s-%s', $associationTypeCode, $entityType);
                $convertedItem[$propertyName] = implode(',', array_column($quantifiedLinks, 'identifier'));
                $convertedItem[sprintf('%s-quantity', $propertyName)] = implode('|', array_column($quantifiedLinks, 'quantity'));
            }
        }

        return $convertedItem;
    }

    private function convertQualityScores(array $scores, array $convertedItem): array
    {
        foreach ($scores as $channel => $localeScores) {
            foreach ($localeScores as $locale => $score) {
                $convertedItem[sprintf('%s-%s-%s', GetProductsWithQualityScoresInterface::FLAT_FIELD_PREFIX, $locale, $channel)] = $score;
            }
        }

        return $convertedItem;
    }
}
