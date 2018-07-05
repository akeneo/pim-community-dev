<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Model;

use Akeneo\Pim\Structure\Component\Model\GroupTypeInterface;
use Akeneo\Tool\Component\Localization\Model\TranslatableInterface;
use Akeneo\Tool\Component\StorageUtils\Model\ReferableInterface;
use Akeneo\Tool\Component\Versioning\Model\VersionableInterface;
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
}
