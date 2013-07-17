<?php

namespace Pim\Bundle\ProductBundle\ImportExport\Reader;

use Pim\Bundle\BatchBundle\Item\ItemReaderInterface;

/**
 * Reader that read all product data from the database
 *
 */
class ProductReader implements ItemReaderInterface
{
    protected $products = null;
    protected $productIterator = null;

    protected $productManager = null;

    public function __construct($productManager)
    {
        $this->productManager = $productManager;
    }

    /**
     * {@inheritDoc}
     */
    public function read()
    {
        if ($this->products == null) {
            $productRepo = $this->productManager->getFlexibleRepository();
            $this->products = $productRepo->findByWithAttributes();
            $this->productsIterator = new \ArrayIterator(new \ArrayObject($this->products));
            $this->productsIterator->rewind();
            echo "Products count:".$this->productsIterator->count()."\n";
        }

        $product = $this->productsIterator->current();
        $this->productsIterator->next();

        return $product;
    }
}
