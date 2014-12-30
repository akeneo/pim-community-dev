<?php
namespace Pim\Bundle\CatalogBundle\Doctrine\Query;

/**
 * Class Cursor
 * @package Pim\Bundle\CatalogBundle\Doctrine\Query
 *
 * @author    Stephane Chapeau <stephane.chapeau@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface CursorInterface
{
    /**
     * @return AbstractQuery
     */
    public function getQueryBuilder();

    /**
     * @return bool
     */
    public function hasNext();

    /**
     * @return array
     */
    public function getNext();

    /**
     * @param $pageSize
     * @return $this
     */
    public function setPageSize($pageSize);

    /**
     * @param $offSet
     * @return $this
     */
    public function setOffSet($offSet);

    /**
     * @return int
     */
    public function getCurrentPage();

    /**
     * @return mixed
     */
    public function getProductCount();

    /**
     * @return float
     */
    public function getPageCount();

    /**
     * @param int $currentPage
     */
    public function setCurrentPage($currentPage);
}
