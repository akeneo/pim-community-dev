<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2017 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Component\Workflow\Model;

use Pim\Component\Catalog\Model\AbstractProductUniqueData;

/**
 * Published product unique data.
 *
 * Product unique data consists of data that is unique among all the products for a given attribute.
 * Only pim_catalog_identifier, pim_catalog_number, pim_catalog_text and pim_catalog_date attribute types can be
 * defined as unique.
 *
 * For instance, if the attribute "release date" is defined as unique.
 * Then, the data "05/24/1980" can be used only once among all the products.
 *
 * @author Damien Carcel (damien.carcel@akeneo.com)
 */
class PublishedProductUniqueData extends AbstractProductUniqueData implements PublishedProductUniqueDataInterface
{
}
