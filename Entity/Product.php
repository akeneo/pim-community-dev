<?php

namespace Pim\Bundle\ProductBundle\Entity;

use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\ExecutionContext;
use Symfony\Component\Validator\Constraints as Assert;

use Oro\Bundle\FlexibleEntityBundle\Entity\Mapping\AbstractEntityFlexible;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Pim\Bundle\ConfigBundle\Entity\Language;

/**
 * Flexible product
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @ORM\Table(name="pim_product")
 * @ORM\Entity(repositoryClass="Pim\Bundle\ProductBundle\Entity\Repository\ProductRepository")
 * @UniqueEntity("sku");
 * @Assert\Callback(methods={"isLanguagesValid"})
 */
class Product extends AbstractEntityFlexible
{
    /**
     * @var string $sku
     *
     * @ORM\Column(name="sku", type="string", length=255, unique=true)
     * @Assert\NotNull()
     */
    protected $sku;

    /**
     * @var Value
     *
     * @ORM\OneToMany(targetEntity="ProductValue", mappedBy="entity", cascade={"persist", "remove"})
     */
    protected $values;

    /**
     * @var productFamily
     *
     * @ORM\ManyToOne(targetEntity="ProductFamily")
     */
    protected $productFamily;

    /**
     * @ORM\OneToMany(targetEntity="ProductLanguage", mappedBy="product", cascade={"persist", "remove"})
     */
    protected $languages;

    /**
     * Redefine constructor
     */
    public function __construct()
    {
        parent::__construct();

        $this->languages = new ArrayCollection;
    }

    /**
     * Get sku
     *
     * @return string
     */
    public function getSku()
    {
        return $this->sku;
    }

    /**
     * Set sku
     *
     * @param string $sku
     *
     * @return \Pim\Bundle\ProductBundle\Entity\Product
     */
    public function setSku($sku)
    {
        $this->sku = $sku;

        return $this;
    }

    /**
     * Get product family
     *
     * @return \Pim\Bundle\ProductBundle\Entity\ProductFamily
     */
    public function getProductFamily()
    {
        return $this->productFamily;
    }

    /**
     * Set product family
     *
     * @param ProductFamily $productFamily
     *
     * @return \Pim\Bundle\ProductBundle\Entity\Product
     */
    public function setProductFamily($productFamily)
    {
        $this->productFamily = $productFamily;

        return $this;
    }

    /**
     * Get languages
     *
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getLanguages()
    {
        return $this->languages;
    }

    /**
     * Get Language
     *
     * @param Language $language
     *
     * @return Language
     */
    public function getLanguage(Language $language)
    {
        return $this->languages->filter(
            function ($l) use ($language) {
                return $language === $l->getLanguage();
            }
        )
        ->first();
    }

    /**
     * Get a collection of active languages
     *
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getActiveLanguages()
    {
        return $this->languages->filter(
            function ($language) {
                return $language->isActive();
            }
        );
    }

    /**
     * Set languages
     *
     * @param ArrayCollection $languages
     *
     * @return \Pim\Bundle\ProductBundle\Entity\Product
     */
    public function setLanguages($languages)
    {
        $this->languages = $languages;

        return $this;
    }

    /**
     * Add language
     *
     * @param Language $language Language
     * @param boolean  $active   Predicate for language activated or not
     *
     * @return \Pim\Bundle\ProductBundle\Entity\Product
     */
    public function addLanguage(Language $language, $active = false)
    {
        $pl = new ProductLanguage;
        $pl->setProduct($this);
        $pl->setLanguage($language);
        $pl->setActive($active);

        $this->languages->add($pl);

        return $this;
    }

    /**
     * Remove language
     *
     * @param Language $language Language
     *
     * @return \Pim\Bundle\ProductBundle\Entity\Product
     */
    public function removeLanguage(Language $language)
    {
        $this->languages->removeElement($this->getLanguage($language));

        return $this;
    }

    /**
     * Get the attributes of the product
     *
     * @return array the attributes of the current product
     */
    public function getAttributes()
    {
        return array_map(
            function ($value) {
                return $value->getAttribute();
            },
            $this->getValues()->toArray()
        );
    }

    /**
     * Get ordered group
     * @return string|number|multitype:
     */
    public function getOrderedGroups()
    {
        $groups = array_map(
            function ($value) {
                return $value->getVirtualGroup();
            },
            $this->getAttributes()
        );
        array_map(
            function($group) { return (string) $group; },
            $groups
        );
        $groups = array_unique($groups);

        usort(
            $groups,
            function ($a, $b) {
                $a = $a->getSortOrder();
                $b = $b->getSortOrder();

                if ($a === $b) {
                    return 0;
                }

                if ($a < $b && $a < 0) {
                    return 1;
                }

                if ($a > $b && $b < 0) {
                    return -1;
                }

                return $a < $b ? -1 : 1;
            }
        );

        return $groups;
    }

    /**
     * Make sure that at least one language has been added to the product
     *
     * @param ExecutionContext $context Execution Context
     */
    public function isLanguagesValid(ExecutionContext $context)
    {
        if ($this->languages->count() == 0) {
            $context->addViolationAtPath(
                $context->getPropertyPath() . '.languages',
                'Please specify at least one activated locale'
            );
        }
    }
}
