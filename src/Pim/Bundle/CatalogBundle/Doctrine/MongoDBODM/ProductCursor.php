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

    protected $cursor=null;

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
        return $this->getCurrentPage()*$this->pageSize<$this->getProductCount();
    }

    /**
     * @return array
     */
    public function getNext()
    {
        if ($this->cursor===null)
            $this->cursor = $this->query->execute();

        $result = [];

        for ($i=0 ; $i<$this->pageSize; $i++)
        {
            $result[] = $this->cursor->getNext();
        }

        $this->currentPage++;

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
