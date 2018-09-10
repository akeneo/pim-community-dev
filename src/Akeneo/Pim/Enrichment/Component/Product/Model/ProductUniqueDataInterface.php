<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Model;

use Akeneo\Pim\Structure\Component\Model\AttributeInterface;

/**
 * Product unique data consists of data that is unique among all the products for a given attribute.
 * Only pim_catalog_identifier, pim_catalog_number, pim_catalog_text and pim_catalog_date attribute types can be
 * defined as unique.
 *
 * For instance, if the attribute "release date" is defined as unique.
 * Then, the data "05/24/1980" can be used only once among all the products.
 *
 * @author    Julien Janvier <julien.janvier@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface ProductUniqueDataInterface
{
    public function getId():int;

    public function getProduct(): ProductInterface;

    public function getAttribute(): AttributeInterface;

    public function getRawData(): string;

    public function setAttribute(AttributeInterface $attribute): void;

    public function setRawData(string $rawData): void;

    public function isEqual(ProductUniqueDataInterface $uniqueValue):bool;
}
