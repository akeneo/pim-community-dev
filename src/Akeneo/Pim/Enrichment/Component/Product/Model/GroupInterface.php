<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Model;

use Doctrine\Common\Collections\ArrayCollection;
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
     */
    public function getId(): int;

    /**
     * Get code
     *
     * @return string $code
     */
    public function getCode(): string;

    /**
     * Set code
     *
     * @param string $code
     */
    public function setCode(string $code): \Akeneo\Pim\Enrichment\Component\Product\Model\GroupInterface;

    /**
     * Set group type
     *
     * @param GroupTypeInterface $type
     */
    public function setType(GroupTypeInterface $type): \Akeneo\Pim\Enrichment\Component\Product\Model\GroupInterface;

    /**
     * Get group type
     */
    public function getType(): \Akeneo\Pim\Structure\Component\Model\GroupTypeInterface;

    /**
     * Get label
     */
    public function getLabel(): string;

    /**
     * Set label
     *
     * @param string $label
     */
    public function setLabel(string $label): \Akeneo\Pim\Enrichment\Component\Product\Model\GroupInterface;

    /**
     * Add a product to the collection (if not already existing)
     *
     * @param ProductInterface $product
     */
    public function addProduct(ProductInterface $product): \Akeneo\Pim\Enrichment\Component\Product\Model\GroupInterface;

    /**
     * Remove a product from the collection
     *
     * @param ProductInterface $product
     */
    public function removeProduct(ProductInterface $product): \Akeneo\Pim\Enrichment\Component\Product\Model\GroupInterface;

    /**
     * Get products collection
     */
    public function getProducts(): ArrayCollection;

    /**
     * Set a products collection
     *
     * @param array $products
     */
    public function setProducts(array $products): \Akeneo\Pim\Enrichment\Component\Product\Model\GroupInterface;
}
