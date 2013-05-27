<?php

namespace Oro\Bundle\SearchBundle\Query;

use Doctrine\Common\Collections\ArrayCollection;
use JMS\Serializer\Annotation\Type;
use JMS\Serializer\Annotation\Exclude;
use BeSimple\SoapBundle\ServiceDefinition\Annotation as Soap;

use Oro\Bundle\SearchBundle\Query\Query;

class Result extends ArrayCollection
{
    /**
     * @Type("Oro\Bundle\SearchBundle\Query\Query")
     * @Exclude
     */
    protected $query;

    /**
     * @Soap\ComplexType("int")
     * @Type("integer")
     * @var integer
     */
    protected $recordsCount;

    /**
     * @Soap\ComplexType("int")
     * @Type("integer")
     * @var integer
     */
    protected $count;

    /**
     * @Soap\ComplexType("Oro\Bundle\SearchBundle\Query\Result\Item[]")
     * @var \Oro\Bundle\SearchBundle\Query\Result\Item[]
     */
    protected $elements;

    /**
     * Initializes a new Result.
     *
     * @param Query   $query
     * @param array   $elements
     * @param integer $recordsCount
     */
    public function __construct(Query $query, array $elements = array(), $recordsCount = 0)
    {
        $this->query = $query;
        $this->recordsCount = $recordsCount;
        parent::__construct($elements);
        $this->count = $this->count();
        $this->elements = $elements;
    }

    /**
     * get Query object
     *
     * @return Query
     */
    public function getQuery()
    {
        return $this->query;
    }

    /**
     * Return number of records of search query without limit parameters
     *
     * @return int
     */
    public function getRecordsCount()
    {
        return $this->recordsCount;
    }

    /**
     * Gets the PHP array representation of this collection.
     * @return array
     */
    public function toSearchResultData()
    {
        $resultData['records_count'] = $this->recordsCount;
        if ($this->count()) {
            $resultData['count'] = $this->count();
            foreach ($this as $resultRecord) {
                $resultData['data'][] = $resultRecord->toArray();
            }
        } else {
            $resultData['count'] = 0;
        }

        return $resultData;
    }

    /**
     * @return Result\Item[]
     */
    public function getElements()
    {
        return $this->elements;
    }
}
