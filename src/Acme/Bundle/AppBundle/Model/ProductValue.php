<?php

namespace Acme\Bundle\AppBundle\Model;

use Acme\Bundle\AppBundle\Entity\Color;
use Acme\Bundle\AppBundle\Entity\Fabric;
use Doctrine\Common\Collections\ArrayCollection;
use Pim\Bundle\CatalogBundle\Model\AbstractProductValue as PimProductValue;

/**
 * Acme override of the product value
 *
 * @author    Julien Janvier <jjanvier@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductValue extends PimProductValue
{
    /** @var ArrayCollection */
    protected $fabrics;

    /** @var array (used only in MongoDB implementation) */
    protected $fabricIds;

    /** @var Color */
    protected $color;

    /**
     * constructor
     */
    public function __construct()
    {
        parent::__construct();

        $this->fabrics = new ArrayCollection();
    }

    /**
     * @return ArrayCollection
     */
    public function getFabrics()
    {
        return $this->fabrics;
    }

    /**
     * @param ArrayCollection $fabrics
     */
    public function setFabrics(ArrayCollection $fabrics)
    {
        $this->fabrics = $fabrics;
    }

    /**
     * @param Fabric $fabric
     */
    public function addFabric(Fabric $fabric)
    {
        $this->fabrics->add($fabric);
    }

    /**
     * @param Fabric $fabric
     */
    public function removeFabric(Fabric $fabric)
    {
        $this->fabrics->removeElement($fabric);
    }

    /**
     * @return Color
     */
    public function getColor()
    {
        return $this->color;
    }

    /**
     * @param Color $color
     */
    public function setColor(Color $color = null)
    {
        $this->color = $color;
    }
}
