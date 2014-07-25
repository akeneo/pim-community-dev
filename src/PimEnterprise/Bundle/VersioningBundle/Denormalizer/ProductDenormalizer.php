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
            foreach ($association->getGroups() as $group) {
                $association->removeGroup($group);
            }

            foreach ($association->getProducts() as $product) {
                $association->removeProduct($product);
            }
        }


        // Get association field names and add associations
        $assocFieldNames = $this->fieldNameBuilder->getAssociationFieldNames();
        foreach ($assocFieldNames as $assocFieldName) {
            if (isset($data[$assocFieldName])) {
                list($associationTypeCode, $part) = explode('-', $assocFieldName);

                $association = $product->getAssociationForTypeCode($associationTypeCode);

                $this->serializer->denormalize(
                    $data[$assocFieldName],
                    'Pim\Bundle\CatalogBundle\Model\Association',
                    'csv',
                    ['entity' => $association, 'association_type_code' => $associationTypeCode, 'part' => $part]
                );

                unset($data[$assocFieldName]);
                $assocFieldName = current($assocFieldNames);

                // TODO: Explode in a method
                if (isset($data[$assocFieldName])) {
                    list($associationTypeCode, $part) = explode('-', $assocFieldName);

                    $this->serializer->denormalize(
                        $data[$assocFieldName],
                        'Pim\Bundle\CatalogBundle\Model\Association',
                        'csv',
                        ['entity' => $association, 'association_type_code' => $associationTypeCode, 'part' => $part]
                    );
                    unset($data[$assocFieldName]);
                    $product->addAssociation($association);
                }
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
            unset($attributeInfos['attribute']);

            // Add attribute to product if not already done
            if (!$product->hasAttribute($attribute)) {
                $this->productBuilder->addAttributeToProduct($product, $attribute);
            }

            // Denormalize data value.
            // The value is already added to the product so automatically updated
            $this->serializer->denormalize(
                $dataValue,
                'Pim\Bundle\CatalogBundle\Model\ProductValue',
                'csv',
                [
                    'product' => $product,
                    'entity' => $product->getValue(
                        $attribute->getCode(),
                        $attributeInfos['locale_code'],
                        $attributeInfos['scope_code']
                    )
                ] + $attributeInfos
            );
        }
    }

    /**
     * @return AssociationTypeRepository
     */
    protected function getAssociationTypeRepository()
    {
        return $this->getRepository($this->assocTypeClass);
    }
}
