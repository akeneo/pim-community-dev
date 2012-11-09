<?php
namespace Pim\Bundle\CatalogBundle\Entity;

use Bap\Bundle\FlexibleEntityBundle\Model\EntityField as AbstractEntityField;
use Bap\Bundle\FlexibleEntityBundle\Model\EntityFieldOption as AbstractEntityFieldOption;
use Doctrine\ORM\Mapping as ORM;

/**
 * Product field as sku, name, etc
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright Copyright (c) 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @ORM\Table(name="Akeneo_PimCatalog_Product_Field")
 * @ORM\Entity
 */
class ProductField extends AbstractEntityField
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
     * @ORM\Column(name="scope", type="integer")
     */
    protected $scope;

    /**
     * @var ArrayCollection $options
     *
     * @ORM\OneToMany(targetEntity="ProductFieldOption", mappedBy="field", cascade={"persist", "remove"})
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
     * @param AbstractEntityFieldOption $option
     * @return AbstractEntityField
     */
    public function addOption(AbstractEntityFieldOption $option)
    {
        $this->options[] = $option;
        $option->setField($this);

        return $this;
    }

}