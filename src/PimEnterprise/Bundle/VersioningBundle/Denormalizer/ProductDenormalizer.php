<?php

namespace PimEnterprise\Bundle\VersioningBundle\Denormalizer;

use Doctrine\Common\Persistence\ManagerRegistry;
use Pim\Bundle\CatalogBundle\Builder\ProductBuilder;
use Pim\Bundle\CatalogBundle\Entity\Repository\AssociationTypeRepository;
use Pim\Bundle\CatalogBundle\Model\AbstractAttribute;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\TransformBundle\Builder\FieldNameBuilder;

/**
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class ProductDenormalizer extends AbstractEntityDenormalizer
{
    const FIELD_ENABLED      = 'enabled';
    const FIELD_FAMILY       = 'family';
    const FIELD_CATEGORIES   = 'categories';
    const FIELD_GROUPS       = 'groups';
//    const FIELD_ASSOCIATIONS = 'associations';
//    const FIELD_VALUES       = 'values';

    /** @var string */
    protected $fieldNameBuilder;

    /** @var ProductBuilder */
    protected $productBuilder;

    /**
     * @param ManagerRegistry  $managerRegistry
     * @param string           $entityClass
     * @param ProductBuilder   $productBuilder
     * @param FieldNameBuilder $fieldNameBuilder
     */
    public function __construct(
        ManagerRegistry $managerRegistry,
        $entityClass,
        ProductBuilder $productBuilder,
        FieldNameBuilder $fieldNameBuilder
    ) {
        parent::__construct($managerRegistry, $entityClass);

        $this->productBuilder   = $productBuilder;
        $this->fieldNameBuilder = $fieldNameBuilder;
    }

    /**
     * {@inheritdoc}
     */
    public function denormalize($data, $class, $format = null, array $context = array())
    {
        $product = $context['entity'];

        foreach ($data as $key => $value) {
            echo $key ." --> ". $value ."<br />";
        }
        $this->printProduct($product);

        if (isset($data[self::FIELD_ENABLED])) {
            $product->setEnabled((bool) $data[self::FIELD_ENABLED]);
            unset($data[self::FIELD_ENABLED]);
        }

        if (isset($data[self::FIELD_FAMILY])) {
            $this->denormalizeFamily($data[self::FIELD_FAMILY], $product);
            unset($data[self::FIELD_FAMILY]);
        }

        if (isset($data[self::FIELD_CATEGORIES])) {
            $this->denormalizeCategories($data[self::FIELD_CATEGORIES], $product);
            unset($data[self::FIELD_CATEGORIES]);
        }

        if (isset($data[self::FIELD_GROUPS])) {
            $this->denormalizeGroups($data[self::FIELD_GROUPS], $product);
            unset($data[self::FIELD_GROUPS]);
        }

        $this->denormalizeAssociations($data, $product);

        $this->denormalizeValues($data, $product);

        $this->printProduct($product);

        return $product;
    }

    /**
     * @param string $data
     * @param ProductInterface $product
     */
    protected function denormalizeFamily($data, ProductInterface $product)
    {
        if (strlen($data) > 0) {
            $family = $this->serializer->denormalize($data, 'Pim\Bundle\CatalogBundle\Entity\Family', 'csv');
        } else {
            $family = null;
        }

        $product->setFamily($family);
    }

    /**
     * @param string $data
     * @param ProductInterface $product
     */
    protected function denormalizeCategories($data, ProductInterface $product)
    {
        // Remove existing categories
        foreach ($product->getCategories() as $category) {
            $product->removeCategory($category);
        }

        // Adding categories
        $categoryCodes = strlen($data) > 0 ? explode(",", $data) : array();
        foreach ($categoryCodes as $categoryCode) {
            $product->addCategory(
                $this->serializer->denormalize($categoryCode, 'Pim\Bundle\CatalogBundle\Entity\Category', 'csv')
            );
        }
    }

    /**
     * @param string           $data
     * @param ProductInterface $product
     */
    protected function denormalizeGroups($data, ProductInterface $product)
    {
        // Remove existing groups
        foreach ($product->getGroups() as $group) {
            $product->removeGroup($group);
        }

        // Adding groups
        $groupCodes = strlen($data) > 0 ? explode(",", $data) : array();
        foreach ($groupCodes as $groupCode) {
            $product->addGroup(
                $this->serializer->denormalize($groupCode, 'Pim\Bundle\CatalogBundle\Entity\Group', 'csv')
            );
        }
    }

    /**
     * @param string           &$data
     * @param ProductInterface $product
     */
    protected function denormalizeAssociations(&$data, ProductInterface $product)
    {
        // Remove existing associations
        foreach ($product->getAssociations() as $association) {
            $product->removeAssociation($association);
        }

        // Get association field names and add associations
        $assocFieldNames = $this->fieldNameBuilder->getAssociationFieldNames();
        foreach ($assocFieldNames as $assocFieldName) {
            if (isset($data[$assocFieldName])) {
                $product->addAssociation(
                    $this->serializer->denormalize($data[$assocFieldName], 'Pim\Bundle\CatalogBundle\Model\Association', 'csv')
                );

                unset($data[$assocFieldName]);
            }
        }
    }

    /**
     * @param                  $data
     * @param ProductInterface $product
     *
     * TODO: parameters should be $data, $format, $context (with product)
     */
    protected function denormalizeValues($data, ProductInterface $product)
    {
        // Remove existing values
        foreach ($product->getValues() as $value) {
            $product->removeValue($value);
        }

        foreach ($data as $attFieldName => $dataValue) {
            $attributeInfos = $this->fieldNameBuilder->extractAttributeFieldNameInfos($attFieldName);
            $attribute = $attributeInfos['attribute'];

            echo "<hr />". $attribute->getCode() ."<br />";

            // Add attribute to product if not already done
            if (!$product->hasAttribute($attribute)) {
                echo "Add attribute to product<br />";
                $this->productBuilder->addAttributeToProduct($product, $attribute);
            }

            // Denormalize data value.
            // The value is already added to the product so automatically updated
            $this->serializer->denormalize(
                $dataValue,
                'Pim\Bundle\CatalogBundle\Model\ProductValue',
                'csv',
                $attributeInfos + ['product' => $product] + ['entity' => $product->getValue($attribute->getCode())]
            );

            echo "- ". $product->getValue($attribute->getCode()) ."<br />";
        }
    }

    /**
     * @return AssociationTypeRepository
     */
    protected function getAssociationTypeRepository()
    {
        return $this->getRepository($this->assocTypeClass);
    }

    protected function printProduct(ProductInterface $product)
    {
        echo "<hr />";
        echo (int) $product->isEnabled() ."<br />";

        if (null !== $product->getFamily()) {
            echo $product->getFamily()->getCode() ."<br />";
        } else {
            echo "NO FAMILY<br />";
        }

        echo "CATEGORIES: ". count($product->getCategories()) ."<br />";
        foreach ($product->getCategories() as $category) {
            echo "- ". $category->getCode() ."<br />";
        }

        echo "GROUPS: ". count($product->getGroups()) ."<br />";
        foreach ($product->getGroups() as $group) {
            echo "- ". $group->getCode() . "<br />";
        }

        echo "ASSOCIATIONS: ". count($product->getAssociations()) ."<br />";
        foreach ($product->getAssociations() as $association) {
            echo "- ". $association->getAssociationType() ."<br />";
        }

        echo "<hr />";
    }
}
