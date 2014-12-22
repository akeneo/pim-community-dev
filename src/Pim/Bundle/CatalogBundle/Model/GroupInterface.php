<?php

namespace Pim\Bundle\CatalogBundle\Model;

use Doctrine\Common\Collections\ArrayCollection;
use Pim\Bundle\CatalogBundle\Entity\GroupType;
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
interface GroupInterface extends TranslatableInterface, GroupSequenceProviderInterface, ReferableInterface,
 VersionableInterface
{
    /**
     * Get the id
     *
     * @return integer
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
     * @param GroupType $type
     *
     * @return GroupInterface
     */
    public function setType(GroupType $type);

    /**
     * Get group type
     *
     * @return GroupType
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
     * TODO : should be re-maned, these attributes are axis
     *
     * @param AttributeInterface $attribute
     *
     * @return GroupInterface
     */
    public function addAttribute(AttributeInterface $attribute);

    /**
     * Remove attribute
     *
     * TODO : should be re-maned, these attributes are axis
     *
     * @param AttributeInterface $attribute
     *
     * @return GroupInterface
     *
     * @throws \InvalidArgumentException
     */
    public function removeAttribute(AttributeInterface $attribute);

    /**
     * Get attributes
     *
     * TODO : should be re-maned, these attributes are axis
     *
     * @return ArrayCollection
     */
    public function getAttributes();

    /**
     * Get attribute ids
     *
     * TODO : should be re-maned, these attributes are axis
     *
     * @return integer[]
     */
    public function getAttributeIds();

    /**
     * Setter for attributes property
     *
     * TODO : should be re-maned, these attributes are axis
     *
     * @param AttributeInterface[] $attributes
     *
     * @return GroupInterface
     */
    public function setAttributes(array $attributes = array());

    /**
     * @return ProductTemplateInterface
     */
    public function getProductTemplate();

    /**
     * @param ProductTemplateInterface $productTemplate
     */
    public function setProductTemplate(ProductTemplateInterface $productTemplate);
}
