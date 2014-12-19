<?php

namespace Pim\Bundle\CatalogBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use JMS\Serializer\Annotation\ExclusionPolicy;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Model\ProductValueInterface;
use Pim\Bundle\CatalogBundle\Model\ProductTemplateInterface;
use Pim\Bundle\CatalogBundle\Model\ReferableInterface;
use Pim\Bundle\TranslationBundle\Entity\AbstractTranslation;
use Pim\Bundle\TranslationBundle\Entity\TranslatableInterface;
use Pim\Bundle\VersioningBundle\Model\VersionableInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\GroupSequenceProviderInterface;

/**
 * Group product template entity, aims to store common product values for products belonging to a group and copy
 * them to  products
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

    /** @var ProductValueInterface[] */
    protected $values;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->values = new ArrayCollection();
    }

    /**
     * @return json
     */
    public function getValuesData()
    {
        return $this->valuesData;
    }

    /**
     * @param json $valuesData
     */
    public function setValuesData($valuesData)
    {
        $this->valuesData = $valuesData;
    }

    /**
     * @return \Pim\Bundle\CatalogBundle\Model\ProductValueInterface[]
     */
    public function getValues()
    {
        return $this->values;
    }

    // TODO : validate forbid to add identifier or axis values

    /**
     * @param \Pim\Bundle\CatalogBundle\Model\ProductValueInterface[] $values
     */
    public function setValues($values)
    {
        $this->values = $values;
    }
}
