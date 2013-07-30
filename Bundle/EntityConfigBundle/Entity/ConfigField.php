<?php

namespace Oro\Bundle\EntityConfigBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

use Doctrine\ORM\PersistentCollection;
use Oro\Bundle\EntityConfigBundle\Entity\AbstractConfig;

/**
 * @ORM\Table(name="oro_config_field")
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks()
 */
class ConfigField extends AbstractConfig
{
    const ENTITY_NAME = 'OroEntityConfigBundle:ConfigField';

    /**
     * @var integer
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;

    /**
     * @var ConfigEntity
     * @ORM\ManyToOne(targetEntity="ConfigEntity", inversedBy="fields")
     * @ORM\JoinColumns({
     * @ORM\JoinColumn(name="entity_id", referencedColumnName="id")
     * })
     */
    protected $entity;

    /**
     * @var ConfigValue[]|PersistentCollection
     * @ORM\OneToMany(targetEntity="ConfigValue", mappedBy="field", cascade={"all"})
     */
    protected $values;

    /**
     * @var string
     * @ORM\Column(type="string", length=255, nullable=false)
     */
    protected $code;

    /**
     * @var string
     * @ORM\Column(type="string", length=60, nullable=false)
     */
    protected $type;

    public function __construct($code = null, $type = null)
    {
        $this->code   = $code;
        $this->type   = $type;
        $this->values = new ArrayCollection;
        $this->mode   = self::MODE_VIEW_DEFAULT;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $code
     * @return $this
     */
    public function setCode($code)
    {
        $this->code = $code;

        return $this;
    }

    /**
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @param string $type
     * @return $this
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param ConfigEntity $entity
     * @return $this
     */
    public function setEntity($entity)
    {
        $this->entity = $entity;

        return $this;
    }

    /**
     * @return ConfigEntity
     */
    public function getEntity()
    {
        return $this->entity;
    }
}
