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
use PimEnterprise\Component\Workflow\Model\PublishedProductValue as PimPublishedProductValue;
use PimEnterprise\Component\Workflow\Model\PublishedProductValueInterface;

/**
 * Acme override of the published product value. In this example:
 *      - "fabrics" is a new many to many relationship
 *      - "color" is a new many to one relationship
 *      - "myNewField" is a new field
 *
 * @author Marie Bochu <marie.bochu@akeneo.com>
 */
class PublishedProductValue extends PimPublishedProductValue implements PublishedProductValueInterface
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
     * @param string             $data
     */
    public function __construct(AttributeInterface $attribute, $channel, $locale, $data)
    {
        parent::__construct($attribute, $channel, $locale, $data);

        $this->fabrics = new ArrayCollection();
        $this->myNewField = rand();
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
