<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\WorkflowBundle\Saver;

use Pim\Bundle\CatalogBundle\Saver\ProductSaver;
use Pim\Component\Resource\Model\BulkSaverInterface;
use Pim\Component\Resource\Model\SaverInterface;

/**
 * Save a product working copy (a classic product with terminology)
 *
 * @author Nicolas Dupont <nicolas@akeneo.com>
 */
class ProductWorkingCopySaver implements SaverInterface, BulkSaverInterface
{
    /** @var  ProductSaver */
    protected $baseProductSaver;

    /**
     * @param ProductSaver $saver
     */
    public function __construct(ProductSaver $saver)
    {
        $this->baseProductSaver = $saver;
    }

    /**
     * {@inheritdoc}
     */
    public function save($product, array $options = [])
    {
        return $this->baseProductSaver->save($product, $options);
    }

    /**
     * {@inheritdoc}
     */
    public function saveAll(array $products, array $options = [])
    {
        return $this->baseProductSaver->saveAll($products, $options);
    }
}
