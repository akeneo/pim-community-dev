<?php
namespace Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM;

use Doctrine\MongoDB\Query\Query;
use Doctrine\ODM\MongoDB\Query\Builder;
use Pim\Bundle\CatalogBundle\Doctrine\Query\AbstractCursor;

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
    protected $query;

    /**
     * @param $query
     */
    public function __construct(Builder $queryBuilder)
    {
        parent::__construct($queryBuilder);
        $this->query = $queryBuilder->getQuery();
    }

    /**
     * @return bool
     */
    public function hasNext()
    {
        return ($this->getCurrentPage()+1)*$this->pageSize<$this->getProductCount();
    }

    /**
     * @return array
     */
    public function getNext()
    {
        $cursor = $this->query->execute()->limit($this->pageSize)->skip($this->offSet*($this->getCurrentPage()));

        $this->currentPage++;

        $result = $cursor->toArray();

        return $result;
    }

    /**
     * @return mixed
     */
    public function getProductCount()
    {
        if ($this->productCount === null) {
            $this->productCount = $this->query->execute()->count();
        }

        return $this->productCount;
    }
}
