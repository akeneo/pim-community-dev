<?php

namespace Pim\Component\Catalog\Updater\Setter;

use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Pim\Bundle\CatalogBundle\Builder\ProductBuilderInterface;
use Pim\Bundle\CatalogBundle\Exception\InvalidArgumentException;
use Pim\Bundle\CatalogBundle\Model\AssociationInterface;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;

/**
 * Sets the association field
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AssociationFieldSetter extends AbstractFieldSetter
{
    /** @var IdentifiableObjectRepositoryInterface */
    protected $productRepository;

    /** @var IdentifiableObjectRepositoryInterface */
    protected $groupRepository;

    /** @var ProductBuilderInterface */
    protected $productBuilder;

    /**
     * @param IdentifiableObjectRepositoryInterface $productRepository
     * @param IdentifiableObjectRepositoryInterface $groupRepository
     * @param ProductBuilderInterface               $productBuilder
     * @param array                                 $supportedFields
     */
    public function __construct(
        IdentifiableObjectRepositoryInterface $productRepository,
        IdentifiableObjectRepositoryInterface $groupRepository,
        ProductBuilderInterface $productBuilder,
        array $supportedFields
    ) {
        $this->productRepository = $productRepository;
        $this->groupRepository   = $groupRepository;
        $this->productBuilder    = $productBuilder;
        $this->supportedFields   = $supportedFields;
    }

    /**
     * {@inheritdoc}
     *
     * Expected data input format :
     * {
     *     "XSELL": {
     *         "groups": ["group1", "group2"],
     *         "products": ["AKN_TS1", "AKN_TSH2"]
     *     },
     *     "UPSELL": {
     *         "groups": ["group3", "group4"],
     *         "products": ["AKN_TS3", "AKN_TSH4"]
     *     },
     * }
     */
    public function setFieldData(ProductInterface $product, $field, $data, array $options = [])
    {
        $this->checkData($field, $data);
        $this->clearAssociations($product);
        $this->addMissingAssociations($product);
        $this->setProductsAndGroupsToAssociations($product, $data);
    }

    /**
     * Clear associations (remove groups and products from existing associations)
     *
     * @param ProductInterface $product
     */
    protected function clearAssociations(ProductInterface $product)
    {
        foreach ($product->getAssociations() as $association) {
            foreach ($association->getGroups() as $group) {
                $association->removeGroup($group);
            }
            foreach ($association->getProducts() as $prod) {
                $association->removeProduct($prod);
            }
        }
    }

    /**
     * Add missing associations (if association type has been added after the last processing)
     *
     * @param ProductInterface $product
     */
    protected function addMissingAssociations(ProductInterface $product)
    {
        $this->productBuilder->addMissingAssociations($product);
    }

    /**
     * Set products and groups to associations
     *
     * @param ProductInterface $product
     */
    protected function setProductsAndGroupsToAssociations(ProductInterface $product, $data)
    {
        foreach ($data as $typeCode => $items) {
            $association = $product->getAssociationForTypeCode($typeCode);
            if (null === $association) {
                throw InvalidArgumentException::expected(
                    'associations',
                    'existing association type code',
                    'setter',
                    'association',
                    $typeCode
                );
            }
            if (isset($items['products'])) {
                $this->setAssociatedProducts($association, $items['products']);
            }
            if (isset($items['groups'])) {
                $this->setAssociatedGroups($association, $items['groups']);
            }
        }
    }

    /**
     * @param AssociationInterface $association
     * @param array                $productsIdentifiers
     */
    protected function setAssociatedProducts(AssociationInterface $association, $productsIdentifiers)
    {
        foreach ($productsIdentifiers as $productIdentifier) {
            $associatedProduct = $this->productRepository->findOneByIdentifier($productIdentifier);
            if (!$associatedProduct) {
                throw InvalidArgumentException::expected(
                    'associations',
                    'existing product identifier',
                    'setter',
                    'association',
                    $productIdentifier
                );
            }
            $association->addProduct($associatedProduct);
        }
    }

    /**
     * @param AssociationInterface $association
     * @param array                $groupsCodes
     */
    protected function setAssociatedGroups(AssociationInterface $association, $groupsCodes)
    {
        foreach ($groupsCodes as $groupCode) {
            $associatedGroup = $this->groupRepository->findOneByIdentifier($groupCode);
            if (!$associatedGroup) {
                throw InvalidArgumentException::expected(
                    'associations',
                    'existing group code',
                    'setter',
                    'association',
                    $groupCode
                );
            }
            $association->addGroup($associatedGroup);
        }
    }

    /**
     * Check if data are valid
     *
     * @param string $field
     * @param mixed  $data
     *
     * @throws InvalidArgumentException
     */
    protected function checkData($field, $data)
    {
        if (!is_array($data)) {
            throw InvalidArgumentException::arrayExpected(
                $field,
                'setter',
                'association',
                gettype($data)
            );
        }

        foreach ($data as $assocTypeCode => $items) {
            $this->checkAssociationData($field, $data, $assocTypeCode, $items);
        }
    }

    /**
     * @param string $field
     * @param array  $data
     * @param string $assocTypeCode
     * @param mixed  $items
     *
     * @throws InvalidArgumentException
     */
    protected function checkAssociationData($field, array $data, $assocTypeCode, $items)
    {
        if (!is_array($items) || !is_string($assocTypeCode) ||
            (!isset($items['products']) && !isset($items['groups']))
        ) {
            throw InvalidArgumentException::associationFormatExpected($field, $data);
        }

        foreach ($items as $itemData) {
            $this->checkAssociationItems($field, $data, $itemData);
        }
    }

    /**
     * @param string $field
     * @param array  $data
     * @param array  $items
     *
     * @throws InvalidArgumentException
     */
    protected function checkAssociationItems($field, array $data, array $items)
    {
        foreach ($items as $code) {
            if (!is_string($code)) {
                throw InvalidArgumentException::associationFormatExpected($field, $data);
            }
        }
    }
}
