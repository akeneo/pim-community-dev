<?php

namespace Pim\Bundle\CatalogBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Pim\Bundle\CatalogBundle\Model\ProductValueInterface;
use Pim\Bundle\CatalogBundle\Model\ProductTemplateInterface;

/**
 * Product template model, contains common product values as raw data
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductTemplate implements ProductTemplateInterface
{
    /** @var integer $id */
    protected $id;

    /** @var Group $group */
    protected $group;

    /** @var json */
    protected $valuesData;

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * {@inheritdoc}
     */
    public function getValuesData()
    {
        return $this->valuesData;
    }

    /**
     * {@inheritdoc}
     */
    public function setValuesData($valuesData)
    {
        $this->valuesData = $valuesData;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function hasValue(ProductValueInterface $value)
    {
        // TODO : will change with json format
        // TODO : should be merged in a class
        $suffix = '';

        if ($value->getAttribute()->isLocalizable()) {
            $suffix = sprintf('-%s', $value->getLocale());
        }
        if ($value->getAttribute()->isScopable()) {
            $suffix .= sprintf('-%s', $value->getScope());
        }

        $field = $value->getAttribute()->getCode() . $suffix;

        return isset($this->valuesData[$field]);
    }
}
