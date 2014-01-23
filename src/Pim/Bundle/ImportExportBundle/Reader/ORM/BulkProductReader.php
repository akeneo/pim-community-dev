<?php

namespace Pim\Bundle\ImportExportBundle\Reader\ORM;

/**
 * Reads all products at once
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class BulkProductReader extends ProductReader
{
    /**
     * {@inheritdoc}
     */
    public function read()
    {
        $products = [];
        while ($product = parent::read()) {
            $products[] = $product;
        }

        return empty($products) ? null : $products;
    }
}
