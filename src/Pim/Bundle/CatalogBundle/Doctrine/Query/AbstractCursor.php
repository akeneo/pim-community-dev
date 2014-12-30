<?php
/**
 * Created by PhpStorm.
 * User: schape
 * Date: 30/12/14
 * Time: 12:04
 */

namespace Pim\Bundle\CatalogBundle\Doctrine\Query;

/**
 * Class AbstractCursor to iterate product in bulk
 *
 * @author    Stephane Chapeau <stephane.chapeau@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
abstract class AbstractCursor implements CursorInterface
{
    /** @var  Query */
    protected $queryBuilder;

    /** @var int  */
    protected $pageSize = 100;

    /** @var int  */
    protected $offSet = 0;

    /** @var int  */
    protected $currentPage = 0;

    /** @var int */
    protected $productCount = null;

    /**
     * @param $query
     */
    public function __construct($queryBuilder)
    {
        $this->queryBuilder = $queryBuilder;
        // TODO : set page size from context
    }

    /**
     * @return AbstractQuery
     */
    public function getQueryBuilder()
    {
        return $this->queryBuilder;
    }

    /**
     * @return mixed
     */
    abstract public function hasNext();

    /**
     * @return mixed
     */
    abstract public function getNext();

    /**
     * @param $pageSize
     * @return $this
     */
    public function setPageSize($pageSize)
    {
        $this->pageSize = $pageSize;

        return $this;
    }

    /**
     * @param $offSet
     * @return $this
     */
    public function setOffSet($offSet)
    {
        $this->offSet = $offSet;

        return $this;
    }

    /**
     * @return int
     */
    public function getCurrentPage()
    {
        return $this->currentPage;
    }

    /**
     * @return int
     */
    public function getProductCount()
    {
        return $this->productCount;
    }

    /**
     * @return float
     */
    public function getPageCount()
    {
        return $this->getProductCount()/$this->pageSize;
    }

    /**
     * @param int $currentPage
     */
    public function setCurrentPage($currentPage)
    {
        $this->currentPage = $currentPage;
    }
}
