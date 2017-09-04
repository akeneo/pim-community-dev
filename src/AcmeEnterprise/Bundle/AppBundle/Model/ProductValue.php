<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace AcmeEnterprise\Bundle\AppBundle\Model;

use AcmeEnterprise\Bundle\AppBundle\Entity\Color;
use AcmeEnterprise\Bundle\AppBundle\Entity\Fabric;
use Doctrine\Common\Collections\ArrayCollection;
use PimEnterprise\Component\Catalog\Model\ProductValue as PimProductValue;

/**
 * Acme override of the product value/ In this example:
 *      - "fabrics" is a new many to many relationship
 *      - "color" is a new many to one relationship
 *      - "myNewField" is a new field
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

    /** @var string */
    protected $myNewField;

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
        if (!$this->fabrics->contains($fabric)) {
            $this->fabrics->add($fabric);
        }
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

    /**
     * @return string
     */
    public function getMyNewField()
    {
        return $this->myNewField;
    }

    /**
     * @param string $myNewField
     */
    public function setMyNewField($myNewField)
    {
        $this->myNewField = $myNewField;
    }
}
