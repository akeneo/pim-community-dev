<?php
namespace Oro\Bundle\DataModelBundle\Entity\Mapping;

use Oro\Bundle\DataModelBundle\Model\Entity\AbstractEntityAttribute;
use Oro\Bundle\DataModelBundle\Model\Entity\AbstractEntityAttributeOption;
use Doctrine\ORM\Mapping as ORM;

/**
 * Base Doctrine ORM entity attribute
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT  MIT
 *
 */
abstract class AbstractOrmEntityAttribute extends AbstractEntityAttribute
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
     * @var string $code
     *
     * @ORM\Column(name="code", type="string", length=255, unique=true)
     */
    protected $code;

    /**
     * @var string $entityType
     *
     * @ORM\Column(name="entity_type", type="string", length=255)
     */
    protected $entityType;

    /**
     * @var string $title
     *
     * @ORM\Column(name="title", type="string", length=255)
     */
    protected $title;

    /**
     * @var datetime $created
     *
     * @ORM\Column(type="datetime")
     */
    protected $created;

    /**
     * @var datetime $updated
     *
     * @ORM\Column(type="datetime")
     */
    protected $updated;

    /**
     * @var AbstractAttributeType $attributeType
     *
     * @ORM\Column(name="attribute_type", type="string", length=255)
     */
    protected $attributeType;

    /**
     * @ORM\Column(name="uniqueValue", type="boolean")
     */
    protected $uniqueValue;

    /**
     * @ORM\Column(name="valueRequired", type="boolean")
     */
    protected $valueRequired;

    /**
     * @ORM\Column(name="searchable", type="boolean")
     */
    protected $searchable;

    /**
     * @ORM\Column(name="translatable", type="boolean")
     */
    protected $translatable;

    /**
     * @var ArrayCollection $options
     *
     * @ORM\OneToMany(targetEntity="AbstractOrmEntityAttributeOption", mappedBy="attribute", cascade={"persist", "remove"}, orphanRemoval=true)
     * @ORM\OrderBy({"sortOrder" = "ASC"})
     */
    protected $options;

    /**
     * Used locale to override Translation listener's locale
     * this is not a mapped attribute of entity metadata, just a simple property
     */
    protected $locale;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->options       = new \Doctrine\Common\Collections\ArrayCollection();
        $this->uniqueValue   = false;
        $this->valueRequired = false;
        $this->searchable    = false;
        $this->translatable  = false;
    }

    /**
     * Add option
     *
     * @param AbstractEntityAttributeOption $option
     *
     * @return AbstractEntityAttribute
     */
    public function addOption(AbstractEntityAttributeOption $option)
    {
        $this->options[] = $option;
        $option->setAttribute($this);

        return $this;
    }

    /**
     * Override to set attribute type class name
     *
     * @param AbstractAttributeType $type
     *
     * @return AbstractEntityAttribute
     */
    public function setAttributeType($type)
    {
        $this->attributeType = get_class($type);

        return $this;
    }

    /**
     * Override to get attribute type instance
     *
     * @return string
     */
    public function getAttributeType()
    {
        $class = $this->attributeType;

        return new $class();
    }

}
