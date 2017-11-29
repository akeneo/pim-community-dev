<?php

namespace Pim\Component\Catalog\Paginator;

use Pim\Component\Catalog\Query\ProductQueryBuilderInterface;
use Doctrine\ODM\MongoDB\Query\Builder;

/**
 * @author    Philippe Mossière <philippe.mossiere@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class ProductPaginator implements \Iterator
{
    private $queryBuilder;

    private $productsPerPage;

    private $products;

    private $pageIndex;

    private $lastProductId;

    public function __construct(ProductQueryBuilderInterface $productQueryBuilder, $productsPerPage = 100)
    {
        $this->productsPerPage = $productsPerPage;
        $this->queryBuilder = $productQueryBuilder->getQueryBuilder();

        if (!$this->queryBuilder instanceof Builder) {
            throw new \RuntimeException('Only MongoDB query builder is supported for this patch.');
        }
    }

    public function current()
    {
        return $this->products;
    }

    public function next()
    {
        $this->pageIndex++;
        $this->loadNextProductsPage();
    }

    public function key()
    {
        return $this->pageIndex;
    }

    public function valid()
    {
        return !empty($this->products);
    }

    public function rewind()
    {
        $this->pageIndex = 0;
        $this->lastProductId = null;
        $this->loadNextProductsPage();
    }

    private function loadNextProductsPage()
    {
        $queryBuilder = clone $this->queryBuilder;

        if (null !== $this->lastProductId) {
            $queryBuilder->field('_id')->gt($this->lastProductId);
        }

        $queryResults = $queryBuilder
            ->sort('_id', 'ASC')
            ->limit($this->productsPerPage)
            ->getQuery()
            ->execute();

        $this->products = [];
        foreach ($queryResults as $product) {
            $this->products[] = $product;
            $this->lastProductId = $product->getId();
        }
    }
}
