<?php

namespace Pim\Bundle\CatalogBundle\Model;

use Doctrine\Common\Collections\ArrayCollection;
use Pim\Bundle\TranslationBundle\Entity\TranslatableInterface;
use Pim\Bundle\VersioningBundle\Model\VersionableInterface;
use Symfony\Component\Validator\GroupSequenceProviderInterface;

/**
 * Group model interface
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface GroupInterface extends
    TranslatableInterface,
    GroupSequenceProviderInterface,
    ReferableInterface,
    VersionableInterface
{
    /**
     * Get the id
     *
     * @return int
     */
    public function getId();

    /**
     * Get code
     *
     * @return string $code
     */
    public function getCode();

    /**
     * Set code
     *
     * @param string $code
     *
     * @return GroupInterface
     */
    public function setCode($code);

    /**
     * Set group type
     *
     * @param GroupTypeInterface $type
     *
     * @return GroupInterface
     */
    public function setType(GroupTypeInterface $type);

    /**
     * Get group type
     *
     * @return GroupTypeInterface
     */
    public function getType();

    /**
     * Get label
     *
     * @return string
     */
    public function getLabel();

    /**
     * Set label
     *
     * @param string $label
     *
     * @return GroupInterface
     */
    public function setLabel($label);

    /**
     * Add a product to the collection (if not already existing)
     *
     * @param ProductInterface $product
     *
     * @return GroupInterface
     */
    public function addProduct(ProductInterface $product);

    /**
     * Remove a product from the collection
     *
     * @param ProductInterface $product
     *
     * @return GroupInterface
     */
    public function removeProduct(ProductInterface $product);

    /**
     * Get products collection
     *
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getProducts();

    /**
     * Set a products collection
     *
     * @param array $products
     *
     * @return GroupInterface
     */
    public function setProducts(array $products);

    /**
     * Add attribute
     *
     * @deprecated will be removed in 1.4, use addAxisAttribute()
     *
     * @param AttributeInterface $attribute
     *
     * @return GroupInterface
     */
    public function addAttribute(AttributeInterface $attribute);

    /**
     * Remove attribute
     *
     * @deprecated will be removed in 1.4, use removeAxisAttribute()
     *
     * @param AttributeInterface $attribute
     *
     * @throws \InvalidArgumentException
     *
     * @return GroupInterface
     */
    public function removeAttribute(AttributeInterface $attribute);

    /**
     * Get attributes
     *
     * @deprecated will be removed in 1.4, use getAxisAttributes()
     *
     * @return ArrayCollection
     */
    public function getAttributes();

    /**
     * Setter for attributes property
     *
     * @deprecated will be removed in 1.4, use setAxisAttributes()
     *
     * @param AttributeInterface[] $attributes
     *
     * @return GroupInterface
     */
    public function setAttributes(array $attributes = array());

    /**
     * Get attribute ids
     *
     * @deprecated will be removed in 1.4
     *
     * @return integer[]
     */
    public function getAttributeIds();

    /**
     * Add axis attribute
     *
     * @param AttributeInterface $attribute
     *
     * @return GroupInterface
     */
    public function addAxisAttribute(AttributeInterface $attribute);

    /**
     * Remove axis attribute
     *
     * @param AttributeInterface $attribute
     *
     * @throws \InvalidArgumentException
     *
     * @return GroupInterface
     */
    public function removeAxisAttribute(AttributeInterface $attribute);

    /**
     * Get axis attributes
     *
     * @return ArrayCollection
     */
    public function getAxisAttributes();

    /**
     * Setter for axis attributes property
     *
     * @param AttributeInterface[] $attributes
     *
     * @return GroupInterface
     */
    public function setAxisAttributes(array $attributes = array());

    /**
     * @return ProductTemplateInterface
     */
    public function getProductTemplate();

    /**
     * @param ProductTemplateInterface $productTemplate
     *
     * @return GroupInterface
     */
    public function setProductTemplate(ProductTemplateInterface $productTemplate);
}
