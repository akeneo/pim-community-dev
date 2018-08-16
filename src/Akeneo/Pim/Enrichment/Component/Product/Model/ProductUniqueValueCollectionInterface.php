<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Model;

/**
 * Business collection interface to handle unique product values.
 *
 * Product unique values consists of data that is unique among all the products for a given attribute.
 * Only pim_catalog_identifier, pim_catalog_number, pim_catalog_text and pim_catalog_date attribute types can be
 * defined as unique.
 *
 * For instance, if the attribute "release date" is defined as unique.
 * Then, the value with the data "05/24/1980" can be used only once among all the products.
 *
 * @author    Julien Janvier <j.janvier@gmail.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface ProductUniqueValueCollectionInterface
{
    /**
     * Gets all unique values of the collection.
     *
     * @return array The unique values in the collection, in the order they
     *               appear in the collection.
     */
    public function getUniqueValues();
}
