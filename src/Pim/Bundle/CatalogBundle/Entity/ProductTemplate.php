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
     * {@inheritdoc}
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
    public function getValues()
    {
        return $this->values;
    }

    // TODO : validate forbid to add identifier or axis values

    /**
     * {@inheritdoc}
     */
    public function setValues($values)
    {
        $this->values = $values;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function hasValue(ProductValueInterface $value)
    {
        // TODO : should be merged
        $suffix = '';

        if ($value->getAttribute()->isLocalizable()) {
            $suffix = sprintf('-%s', $value->getLocale());
        }
        if ($value->getAttribute()->isScopable()) {
            $suffix .= sprintf('-%s', $value->getScope());
        }

        $field = $value->getAttribute()->getCode() . $suffix;

        // TODO : will change with json format
        return isset($this->valuesData[$field]);
    }
}
