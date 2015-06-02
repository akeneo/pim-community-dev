<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\CatalogBundle\Command;

use Pim\Bundle\CatalogBundle\Command\UpdateProductCommand as BaseUpdateProductCommand;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;

/**
 * Updates a product
 *
 * @author Marie Bochu <marie.bochu@akeneo.com>
 */
class UpdateProductCommand extends BaseUpdateProductCommand
{
    /**
     * {@inheritdoc}
     *
     * Call DelegatingProductSaver to save product if user is owner of product but save a draft if not
     */
    protected function save(ProductInterface $product)
    {
        $saver = $this->getContainer()->get('pimee_workflow.saver.product_delegating');
        $saver->save($product);
    }
}
