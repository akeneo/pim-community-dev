<?php
namespace Pim\Bundle\CatalogBundle\Entity;

use Bap\Bundle\FlexibleEntityBundle\Model\Entity as AbstractEntity;
use Bap\Bundle\FlexibleEntityBundle\Model\EntityValue as AbstractEntityValue;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * Flexible product
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright Copyright (c) 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @ORM\Table(name="Akeneo_PimCatalog_Product_Entity")
 * @ORM\Entity
 */
class ProductEntity extends AbstractEntity
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
     * @var EntityType $type
     *
     * @ORM\ManyToOne(targetEntity="ProductType")
     */
    protected $type;

    /**
     * @var Value
     *
     * @ORM\OneToMany(targetEntity="ProductValue", mappedBy="product", cascade={"persist", "remove"})
     */
    protected $values;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->values = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Add value
     *
     * @param AbstractEntityValue $value
     * @return AbstractEntity
     */
    public function addValue(AbstractEntityValue $value)
    {
        $this->values[] = $value;
        $value->setEntity($this);

        return $this;
    }

}