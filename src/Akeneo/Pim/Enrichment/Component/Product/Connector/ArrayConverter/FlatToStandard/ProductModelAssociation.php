<?php
declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Connector\ArrayConverter\FlatToStandard;

use Akeneo\Tool\Component\Connector\ArrayConverter\ArrayConverterInterface;

/**
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2018 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductModelAssociation implements ArrayConverterInterface
{
    /** @var ArrayConverterInterface */
    protected $productModelConverter;

    /**
     * @param ArrayConverterInterface $converter
     */
    public function __construct(ArrayConverterInterface $converter)
    {
        $this->productModelConverter = $converter;
    }

    /**
     * {@inheritdoc}
     *
     * Convert flat array to structured array by keeping only identifier and associations
     *
     * Before:
     * [
     *     'code': 'hiroshima',
     *     'name-fr_FR': 'T-shirt super beau',
     *     'description-en_US-mobile': 'My description',
     *     'length': '10 CENTIMETER',
     *     'XSELL-groups': 'akeneo_tshirt, oro_tshirt',
     *     'XSELL-product': 'AKN_TS, ORO_TSH'
     * ]
     *
     * After:
     * {
     *      "code": "hiroshima",
     *      "associations": {
     *          "XSELL": {
     *              "groups": ["akeneo_tshirt", "oro_tshirt"],
     *              "products": ["AKN_TS", "ORO_TSH"]
     *          }
     *      }
     * }
     */
    public function convert(array $item, array $options = []): array
    {
        $convertedItem = $this->productModelConverter->convert($item, $options);

        return $this->filter($convertedItem);
    }

    /**
     * Filters the item to keep only association related fields
     *
     * @param array $item
     *
     * @return array
     */
    protected function filter(array $item): array
    {
        $allowed = ['code', 'associations'];

        return array_filter($item, function ($key) use ($allowed) {
            return in_array($key, $allowed);
        }, ARRAY_FILTER_USE_KEY);
    }
}
