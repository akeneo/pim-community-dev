<?php

namespace Oro\Bundle\SearchBundle\Query\Result;

use Doctrine\Common\Persistence\ObjectManager;
use JMS\Serializer\Annotation\Type;
use JMS\Serializer\Annotation\Exclude;
use BeSimple\SoapBundle\ServiceDefinition\Annotation as Soap;

class Item
{
    /**
     * @var string
     * @Type("string")
     * @Soap\ComplexType("string")
     */
    protected $entityName;

    /**
     * @var int
     * @Type("integer")
     * @Soap\ComplexType("int")
     */
    protected $recordId;

    /**
     * @Soap\ComplexType("string")
     * @var string
     */
    protected $recordTitle;

    /**
     * @Soap\ComplexType("string")
     * @var string
     */
    protected $recordUrl;

    /**
     * @var string
     */
    protected $recordText;

    /**
     * @var array
     */
    protected $entityConfig;

    /**
     * @var \Doctrine\Common\Persistence\ObjectManager
     * @Exclude
     */
    protected $em;

    public function __construct(
        ObjectManager $em,
        $entityName = null,
        $recordId = 0,
        $recordTitle = null,
        $recordUrl = null,
        $recordText = '',
        $entityConfig = array()
    ) {
        $this->em = $em;
        if ($entityName) {
            $this->setEntityName($entityName);
        }
        if ($recordId) {
            $this->setRecordId($recordId);
        }
        $this->recordTitle = $recordTitle;
        $this->recordUrl = $recordUrl;
        $this->recordText = $recordText;
        $this->entityConfig = $entityConfig;
    }

    /**
     * Set entity name
     *
     * @param  string                                     $entityName
     * @return \Oro\Bundle\SearchBundle\Query\Result\Item
     */
    public function setEntityName($entityName)
    {
        $this->entityName = $entityName;

        return $this;
    }

    /**
     * Set record id
     *
     * @param $recordId
     * @return \Oro\Bundle\SearchBundle\Query\Result\Item
     */
    public function setRecordId($recordId)
    {
        $this->recordId = $recordId;

        return $this;
    }

    /**
     * Get entity name
     *
     * @return string
     */
    public function getEntityName()
    {
        return $this->entityName;
    }

    /**
     * Get record id
     *
     * @return int
     */
    public function getRecordId()
    {
        return $this->recordId;
    }

    /**
     * Load related object
     * @return object
     */
    public function getEntity()
    {
        return $this->em->getRepository($this->getEntityName())->find($this->getRecordId());
    }

    /**
     * Set record title
     *
     * @param string $recordTitle
     *
     * @return Item
     */
    public function setRecordTitle($recordTitle)
    {
        $this->recordTitle = $recordTitle;

        return $this;
    }

    /**
     * Get record string
     *
     * @return string
     */
    public function getRecordTitle()
    {
        return $this->recordTitle;
    }

    /**
     * Set record string
     *
     * @param string $recordUrl
     *
     * @return Item
     */
    public function setRecordUrl($recordUrl)
    {
        $this->recordUrl = $recordUrl;

        return $this;
    }

    /**
     * Get record url
     *
     * @return string
     */
    public function getRecordUrl()
    {
        return $this->recordUrl;
    }

    /**
     * Set record string data
     *
     * @param string $recordText
     *
     * @return Item
     */
    public function setRecordText($recordText)
    {
        $this->recordText = $recordText;

        return $this;
    }

    /**
     * Get record string data
     *
     * @return string
     */
    public function getRecordText()
    {
        return $this->recordText;
    }

    /**
     * Get entity mapping config array
     *
     * @return array
     */
    public function getEntityConfig()
    {
        return $this->entityConfig;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return array(
            'entity_name'   => $this->entityName,
            'record_id'     => $this->recordId,
            'record_string' => $this->getRecordTitle(),
            'record_url'    => $this->getRecordUrl(),
        );
    }
}
