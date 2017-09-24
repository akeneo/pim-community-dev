<?php

namespace Pim\Component\Catalog\Model;

use Akeneo\Component\Localization\Model\TranslatableInterface;
use Akeneo\Component\Versioning\Model\VersionableInterface;
use Doctrine\Common\Collections\ArrayCollection;
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
     * Add axis attribute
     *
     * @deprecated will be remove with 2.0
     *
     * @param AttributeInterface $axisAttribute
     *
     * @return GroupInterface
     */
    public function addAxisAttribute(AttributeInterface $axisAttribute);

    /**
     * Remove axis attribute
     *
     * @deprecated will be remove with 2.0
     *
     * @param AttributeInterface $axisAttribute
     *
     * @throws \InvalidArgumentException
     *
     * @return GroupInterface
     */
    public function removeAxisAttribute(AttributeInterface $axisAttribute);

    /**
     * Get axis attributes
     *
     * @deprecated will be remove with 2.0
     *
     * @return ArrayCollection
     */
    public function getAxisAttributes();

    /**
     * Setter for axis attributes property
     *
     * @deprecated will be remove with 2.0
     *
     * @param AttributeInterface[] $axisAttributes
     *
     * @return GroupInterface
     */
    public function setAxisAttributes(array $axisAttributes = []);

    /**
     * @deprecated will be remove with 2.0
     *
     * @return ProductTemplateInterface
     */
    public function getProductTemplate();

    /**
     * @deprecated will be remove with 2.0
     *
     * @param ProductTemplateInterface $productTemplate
     *
     * @return GroupInterface
     */
    public function setProductTemplate(ProductTemplateInterface $productTemplate);
}
