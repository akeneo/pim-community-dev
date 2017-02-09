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
use Pim\Component\Catalog\Model\AttributeInterface;
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

    /** @var Color */
    protected $color;

    /** @var string */
    protected $myNewField;

    /**
     * @param AttributeInterface $attribute
     * @param string             $channel
     * @param string             $locale
     * @param mixed              $data
     */
    public function __construct(AttributeInterface $attribute, $channel, $locale, $data)
    {
        parent::__construct($attribute, $channel, $locale, $data);
    }

    /**
     * @return ArrayCollection
     */
    public function getFabrics()
    {
        return $this->fabrics;
    }

    /**
     * @return Color
     */
    public function getColor()
    {
        return $this->color;
    }

    /**
     * @return string
     */
    public function getMyNewField()
    {
        return $this->myNewField;
    }

    /**
     * @param ArrayCollection $fabrics
     */
    protected function setFabrics(ArrayCollection $fabrics)
    {
        $this->fabrics = $fabrics;
    }

    /**
     * @param Color $color
     */
    protected function setColor(Color $color = null)
    {
        $this->color = $color;
    }

    /**
     * @param string $myNewField
     */
    protected function setMyNewField($myNewField)
    {
        $this->myNewField = $myNewField;
    }
}
