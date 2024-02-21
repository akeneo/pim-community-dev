<?php

namespace Oro\Bundle\ConfigBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(
 *  name="oro_config",
 *  uniqueConstraints={@ORM\UniqueConstraint(name="CONFIG_UQ_ENTITY", columns={"entity", "record_id"})}
 * )
 * @ORM\Entity(repositoryClass="Oro\Bundle\ConfigBundle\Entity\Repository\ConfigRepository")
 */
class Config
{
    /**
     * @var int
     *
     * @ORM\Id
     * @ORM\Column(name="id", type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(name="entity", type="string", length=255, nullable=true)
     */
    protected $scopedEntity;

    /**
     * @var int
     *
     * @ORM\Column(name="record_id", type="integer", nullable=true)
     */
    protected $recordId;

    /**
     * @var array
     *
     * @ORM\OneToMany(targetEntity="ConfigValue", mappedBy="config",
     *      cascade={"persist", "remove"}, orphanRemoval=true)
     */
    protected $values;

    public function __construct()
    {
        $this->values = new ArrayCollection();
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
     * Get entity
     *
     * @return string
     */
    public function getEntity()
    {
        return $this->scopedEntity;
    }

    /**
     * Set entity
     *
     * @param  string $entity
     *
     * @return Config
     */
    public function setEntity($entity)
    {
        $this->scopedEntity = $entity;

        return $this;
    }

    /**
     * Get record id
     *
     * @return integer
     */
    public function getRecordId()
    {
        return $this->recordId;
    }

    /**
     * Set record id
     *
     * @param  integer $recordId
     *
     * @return Config
     */
    public function setRecordId($recordId)
    {
        $this->recordId = $recordId;

        return $this;
    }

    /**
     * Returns array of entity settings
     *
     * @return array Entity related settings
     */
    public function getValues()
    {
        return $this->values;
    }

    /**
     * @param $values
     * @return $this
     */
    public function setValues($values)
    {
        $this->values = $values;

        return $this;
    }

    public function getOrCreateValue($section, $key)
    {
        $value = $this->getValues()->filter(
            function (ConfigValue $item) use ($key, $section) {
                return $item->getName() == $key && $item->getSection() == $section;
            }
        );

        if ($value->first() === false) {
            $value = new ConfigValue();
            $value->setConfig($this)
                ->setName($key)
                ->setSection($section);
        } else {
            $value = $value->first();
        }

        return $value;
    }
}
