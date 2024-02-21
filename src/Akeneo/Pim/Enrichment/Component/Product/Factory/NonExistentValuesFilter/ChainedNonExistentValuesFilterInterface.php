<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Factory\NonExistentValuesFilter;

/**
 * As we store the values in JSON, the consistency of the data is not guaranteed.
 * It means that attribute codes, option codes, locale codes, channels codes (etc) in the values
 * can reference data that are not existing any more.
 *
 * For example, if you delete an option code, it will still be referenced in the product value of a product.
 * The values are not cleaned in live (in an ideal world, it should).
 * The goal of this class is to filter those non existent data.
 *
 * In order to minimize the number of requests, we accept several collection of raw values corresponding to several entities.
 * For example, several 100 raw values of 100 different products. It allows to filter all non existent file (for example) of this
 * 100 products with only one request. We avoid the 1+n problem this way. It drastically improves the performance.
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface ChainedNonExistentValuesFilterInterface
{
    /**
     * @param array $rawValuesCollection it take several raw values in input of several entities in order to batch requests
     *
     * @return array $rawValuesCollection without all non existent data (removed channel, option code, etc)
     */
    public function filterAll(array $rawValuesCollection): array;
}
