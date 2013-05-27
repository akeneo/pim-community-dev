<?php

namespace Oro\Bundle\SearchBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * QueryLog
 *
 * @ORM\Table(name="oro_search_query")
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 */
class Query
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="entity", type="string", length=250)
     */
    private $entity;

    /**
     * @var string
     *
     * @ORM\Column(name="query", type="text")
     */
    private $query;

    /**
     * @var integer
     *
     * @ORM\Column(name="result_count", type="integer")
     */
    private $resultCount;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created_at", type="datetime")
     */
    private $createdAt;

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set from
     *
     * @param  string   $entity
     * @return QueryLog
     */
    public function setEntity($entity)
    {
        $this->entity = $entity;

        return $this;
    }

    /**
     * Get from
     *
     * @return string
     */
    public function getEntity()
    {
        return $this->entity;
    }

    /**
     * Set query
     *
     * @param  string   $query
     * @return QueryLog
     */
    public function setQuery($query)
    {
        $this->query = $query;

        return $this;
    }

    /**
     * Get query
     *
     * @return string
     */
    public function getQuery()
    {
        return $this->query;
    }

    /**
     * Set resultCount
     *
     * @param  integer  $resultCount
     * @return QueryLog
     */
    public function setResultCount($resultCount)
    {
        $this->resultCount = $resultCount;

        return $this;
    }

    /**
     * Get resultCount
     *
     * @return integer
     */
    public function getResultCount()
    {
        return $this->resultCount;
    }

    /**
     * Pre persist event listener
     * @ORM\PrePersist
     */
    public function beforeSave()
    {
        $this->createdAt = new \DateTime('now', new \DateTimeZone('UTC'));
    }

    /**
     * Set createdAt
     *
     * @param  \DateTime $createdAt
     * @return Query
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * Get createdAt
     *
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }
}
