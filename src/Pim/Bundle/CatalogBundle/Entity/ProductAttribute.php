<?php
namespace Pim\Bundle\CatalogBundle\Entity;

use Bap\Bundle\FlexibleEntityBundle\Model\EntityAttribute as AbstractEntityAttribute;
use Bap\Bundle\FlexibleEntityBundle\Model\EntityAttributeOption as AbstractEntityAttributeOption;
use Doctrine\ORM\Mapping as ORM;

/**
 * Product attribute as sku, name, etc
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright Copyright (c) 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @ORM\Table(name="Akeneo_PimCatalog_Product_Attribute")
 * @ORM\Entity
 */
class ProductAttribute extends AbstractEntityAttribute
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
     * @ORM\Column(name="scope", type="integer")
     */
    protected $scope;

    /**
     * @var ArrayCollection $options
     *
     * @ORM\OneToMany(targetEntity="ProductAttributeOption", mappedBy="attribute", cascade={"persist", "remove"}, orphanRemoval=true)
     * @ORM\OrderBy({"sortOrder" = "ASC"})
     */
    protected $options;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->options = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Add option
     *
     * @param AbstractEntityAttributeOption $option
     * @return AbstractEntityAttribute
     */
    public function addOption(AbstractEntityAttributeOption $option)
    {
        $this->options[] = $option;
        $option->setAttribute($this);

        return $this;
    }

}