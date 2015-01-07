<?php
namespace Pim\Bundle\CatalogBundle\Doctrine\ORM;

use Doctrine\MongoDB\Query\Query;
use Doctrine\ORM\QueryBuilder;
use Pim\Bundle\CatalogBundle\Doctrine\Query\AbstractCursor;
use Doctrine\ORM\Tools\Pagination\Paginator;

/**
 * Class ProductCursor to iterate product in bulk
 *
 * @author    Stephane Chapeau <stephane.chapeau@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
  */
class ProductCursor extends AbstractCursor
{

    /** @var  Query */
    protected $queryBuilder;

    /** @var  Paginator */
    protected $paginator;

    /**
     * @param $query
     */
    public function __construct(QueryBuilder $queryBuilder)
    {
        parent::__construct($queryBuilder);
        $this->queryBuilder = $queryBuilder->getQuery();
    }

    /**
     * @return bool
     */
    public function hasNext()
    {
        return $this->getCurrentPage()*$this->pageSize<$this->getProductCount();
    }

    /**
     * @return array
     */
    public function getNext()
    {
        $result = [];
        if ($this->getProductCount()>0) {
            $this->paginator
                ->getQuery()
                ->setFirstResult($this->getOffSet())
                ->setMaxResults($this->pageSize);

            $this->currentPage++;

            $result = $this->paginator->getIterator()->getArraycopy();
        }

        return $result;
    }

    /**
     * @return mixed
     */
    public function getProductCount()
    {
        if ($this->productCount === null) {

            $this->paginator = new Paginator($this->queryBuilder, $fetchJoinCollection = true);
            $this->productCount = count($this->paginator);
        }

        return $this->productCount;
    }
}
