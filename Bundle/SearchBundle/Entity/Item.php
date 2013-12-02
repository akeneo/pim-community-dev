<?php

namespace Oro\Bundle\SearchBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

use Oro\Bundle\SearchBundle\Engine\Indexer;

/**
 * Search index items that correspond to specific entity record
 *
 * @ORM\Table(
 *  name="oro_search_item",
 *  uniqueConstraints={@ORM\UniqueConstraint(name="IDX_ENTITY", columns={"entity", "record_id"})},
 *  indexes={@ORM\Index(name="IDX_ALIAS", columns={"alias"}), @ORM\Index(name="IDX_ENTITIES", columns={"entity"})}
 * )
 * @ORM\Entity(repositoryClass="Oro\Bundle\SearchBundle\Entity\Repository\SearchIndexRepository")
 * @ORM\HasLifecycleCallbacks
 */
class Item
{
    /**
     * @var integer $id
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string $entity
     *
     * @ORM\Column(name="entity", type="string", length=255)
     */
    protected $entity;

    /**
     * @var string $alias
     *
     * @ORM\Column(name="alias", type="string", length=255)
     */
    protected $alias;

    /**
     * @var integer $record_id
     *
     * @ORM\Column(name="record_id", type="integer", nullable=true)
     */
    protected $recordId;

    /**
     * @var string $title
     * @ORM\Column(name="title", type="string", length=255, nullable=true)
     */
    protected $title;

    /**
     * @var bool $changed
     *
     * @ORM\Column(name="changed", type="boolean", options={"unsigned"=true})
     */
    protected $changed = false;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created_at", type="datetime")
     */
    protected $createdAt;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="updated_at", type="datetime")
     */
    protected $updatedAt;

    /**
     * @ORM\OneToMany(targetEntity="IndexText", mappedBy="item", cascade={"all"}, orphanRemoval=true)
     */
    private $textFields;

    /**
     * @ORM\OneToMany(targetEntity="IndexInteger", mappedBy="item", cascade={"all"}, orphanRemoval=true)
     */
    private $integerFields;

    /**
     * @ORM\OneToMany(targetEntity="IndexDecimal", mappedBy="item", cascade={"all"}, orphanRemoval=true)
     */
    private $decimalFields;

    /**
     * @ORM\OneToMany(targetEntity="IndexDatetime", mappedBy="item", cascade={"all"}, orphanRemoval=true)
     */
    private $datetimeFields;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->textFields = new ArrayCollection();
        $this->integerFields = new ArrayCollection();
        $this->decimalFields = new ArrayCollection();
        $this->datetimeFields = new ArrayCollection();
    }

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
     * Set entity
     *
     * @param  string $entity
     *
     * @return Item
     */
    public function setEntity($entity)
    {
        $this->entity = $entity;

        return $this;
    }

    /**
     * Get entity
     *
     * @return string
     */
    public function getEntity()
    {
        return $this->entity;
    }

    /**
     * Set recordId
     *
     * @param  integer $recordId
     *
     * @return Item
     */
    public function setRecordId($recordId)
    {
        $this->recordId = $recordId;

        return $this;
    }

    /**
     * Get recordId
     *
     * @return integer
     */
    public function getRecordId()
    {
        return $this->recordId;
    }

    /**
     * Set changed
     *
     * @param  boolean $changed
     *
     * @return Item
     */
    public function setChanged($changed)
    {
        $this->changed = (bool)$changed;

        return $this;
    }

    /**
     * Get changed
     *
     * @return boolean
     */
    public function getChanged()
    {
        return $this->changed;
    }

    /**
     * Add integerFields
     *
     * @param  IndexInteger $integerFields
     *
     * @return Item
     */
    public function addIntegerField(IndexInteger $integerFields)
    {
        $this->integerFields[] = $integerFields;

        return $this;
    }

    /**
     * Remove integerFields
     *
     * @param IndexInteger $integerFields
     */
    public function removeIntegerField(IndexInteger $integerFields)
    {
        $this->integerFields->removeElement($integerFields);
    }

    /**
     * Get integerFields
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getIntegerFields()
    {
        return $this->integerFields;
    }

    /**
     * Add decimalFields
     *
     * @param  IndexDecimal $decimalFields
     *
     * @return Item
     */
    public function addDecimalField(IndexDecimal $decimalFields)
    {
        $this->decimalFields[] = $decimalFields;

        return $this;
    }

    /**
     * Remove decimalFields
     *
     * @param IndexDecimal $decimalFields
     */
    public function removeDecimalField(IndexDecimal $decimalFields)
    {
        $this->decimalFields->removeElement($decimalFields);
    }

    /**
     * Get decimalFields
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getDecimalFields()
    {
        return $this->decimalFields;
    }

    /**
     * Add datetimeFields
     *
     * @param  IndexDatetime $datetimeFields
     *
     * @return Item
     */
    public function addDatetimeField(IndexDatetime $datetimeFields)
    {
        $this->datetimeFields[] = $datetimeFields;

        return $this;
    }

    /**
     * Remove datetimeFields
     *
     * @param  IndexDatetime $datetimeFields
     *
     * @return Item
     */
    public function removeDatetimeField(IndexDatetime $datetimeFields)
    {
        $this->datetimeFields->removeElement($datetimeFields);

        return $this;
    }

    /**
     * Get datetimeFields
     *
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getDatetimeFields()
    {
        return $this->datetimeFields;
    }

    /**
     * Add text fields
     *
     * @param  IndexText $textFields
     *
     * @return Item
     */
    public function addTextField(IndexText $textFields)
    {
        $this->textFields[] = $textFields;

        return $this;
    }

    /**
     * Remove text fields
     *
     * @param  IndexText $textFields
     *
     * @return Item
     */
    public function removeTextField(IndexText $textFields)
    {
        $this->textFields->removeElement($textFields);

        return $this;
    }

    /**
     * Get text fields
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getTextFields()
    {
        return $this->textFields;
    }

    /**
     * Pre persist event listener
     * @ORM\PrePersist
     */
    public function beforeSave()
    {
        $this->createdAt = new \DateTime('now', new \DateTimeZone('UTC'));
        $this->updatedAt = new \DateTime('now', new \DateTimeZone('UTC'));
    }

    /**
     * Pre update event listener
     * @ORM\PreUpdate
     */
    public function beforeUpdate()
    {
        $this->updatedAt = new \DateTime('now', new \DateTimeZone('UTC'));
    }

    /**
     * Set createdAt
     *
     * @param  \DateTime $createdAt
     *
     * @return Item
     */
    public function setCreatedAt(\DateTime $createdAt)
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

    /**
     * Set updatedAt
     *
     * @param \DateTime $updatedAt
     *
     * @return Item
     */
    public function setUpdatedAt(\DateTime $updatedAt)
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * Get updatedAt
     *
     * @return \DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * Save index item data
     *
     * @param array $objectData
     *
     * @return Item
     */
    public function saveItemData($objectData)
    {
        $this->saveData($objectData, $this->textFields, new IndexText(), 'text');
        $this->saveData($objectData, $this->integerFields, new IndexInteger(), 'integer');
        $this->saveData($objectData, $this->datetimeFields, new IndexDatetime(), 'datetime');
        $this->saveData($objectData, $this->decimalFields, new IndexDecimal(), 'decimal');

        return $this;
    }

    /**
     * Set alias
     *
     * @param  string $alias
     *
     * @return Item
     */
    public function setAlias($alias)
    {
        $this->alias = $alias;

        return $this;
    }

    /**
     * Get alias
     *
     * @return string
     */
    public function getAlias()
    {
        return $this->alias;
    }

    /**
     * Set title
     *
     * @param  string $title
     *
     * @return Item
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    public function getRecordText()
    {
        $recordText = '';
        foreach ($this->textFields as $textField) {
            if ($textField->getField() == Indexer::TEXT_ALL_DATA_FIELD) {
                $recordText = $textField->getValue();
            }
        }

        return $recordText;
    }

    /**
     * @param array  $objectData
     * @param object $fields
     * @param object $newRecord
     * @param string $type
     */
    protected function saveData($objectData, $fields, $newRecord, $type)
    {
        if (isset($objectData[$type]) && count($objectData[$type])) {
            $itemData = $objectData[$type];
            $updatedTextFields = array();
            foreach ($itemData as $fieldName => $fieldData) {
                foreach ($fields as $index => $collectionElement) {
                    //update fields
                    if ($fieldName == $collectionElement->getField()) {
                        $collectionElement->setValue($fieldData);
                        $updatedTextFields[$index] = $index;
                        unset($itemData[$fieldName]);
                    }
                }
            }
            //delete fields
            if (count($updatedTextFields) < count($this->textFields)) {
                foreach ($this->textFields as $index => $collectionElement) {
                    if (!array_key_exists($index, $updatedTextFields)) {
                        $fields->removeElement($collectionElement);
                    }
                }
            }
            //add new fields
            if (isset($itemData) && count($itemData)) {
                foreach ($itemData as $fieldName => $fieldData) {
                    $record = clone $newRecord;
                    $this->setFieldData($record, $fieldName, $fieldData);
                    $fields[] = $record;
                }
            }
        }
    }

    /**
     * Set record parameters
     *
     * @param object $record
     * @param string $fieldName
     * @param mixed  $fieldData
     */
    private function setFieldData($record, $fieldName, $fieldData)
    {
        $record->setField($fieldName)
            ->setValue($fieldData)
            ->setItem($this);
    }
}
