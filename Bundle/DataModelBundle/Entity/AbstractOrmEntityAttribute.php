<?php
namespace Oro\Bundle\DataModelBundle\Entity;

use Oro\Bundle\DataModelBundle\Model\AbstractEntityAttribute;
use Oro\Bundle\DataModelBundle\Model\AbstractEntityAttributeOption;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

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
     * @var string $title
     *
     * @ORM\Column(name="title", type="string", length=255)
     */
    protected $title;

    /**
     * @var datetime $created
     *
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(type="datetime")
     */
    protected $created;

    /**
     * @var datetime $updated
     *
     * @Gedmo\Timestampable(on="update")
     * @ORM\Column(type="datetime")
     */
    protected $updated;

    /**
     * @var string $type
     *
     * @ORM\Column(name="type", type="string", length=255)
     */
    protected $type;

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
     * @ORM\OneToMany(targetEntity="EntityAttributeOption", mappedBy="attribute", cascade={"persist", "remove"}, orphanRemoval=true)
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

}
