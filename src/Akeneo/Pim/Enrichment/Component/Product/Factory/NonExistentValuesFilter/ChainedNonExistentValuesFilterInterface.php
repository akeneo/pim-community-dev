<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Factory\NonExistentValuesFilter;

/**
 * As we store the values in JSON, the consistency of teh data is not guaranteed.
 * It means that attribute codes, option codes, locale codes, channels codes (etc) in the values
 * can reference data that are not existing any more.
 *
 * For example, if you delete an option code, it will still be referenced in the product value of a product.
 * The values are not cleaned in live (in an ideal world, it should).
 * The goal of this class is to filter those non existent data, by batching requests.
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface ChainedNonExistentValuesFilterInterface
{
    public function filterAll(OnGoingFilteredRawValues $onGoingFilteredRawValues): OnGoingFilteredRawValues;
}
