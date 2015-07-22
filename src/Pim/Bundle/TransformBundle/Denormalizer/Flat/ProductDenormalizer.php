<?php

namespace Pim\Bundle\TransformBundle\Denormalizer\Flat;

use Doctrine\Common\Persistence\ManagerRegistry;
use Pim\Bundle\CatalogBundle\Builder\ProductBuilder;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\TransformBundle\Builder\FieldNameBuilder;

/**
 * Product flat denormalizer
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductDenormalizer extends AbstractEntityDenormalizer
{
    /** @staticvar string */
    const FIELD_ENABLED      = 'enabled';

    /** @staticvar string */
    const FIELD_FAMILY       = 'family';

    /** @staticvar string */
    const FIELD_CATEGORIES   = 'categories';

    /** @staticvar string */
    const FIELD_GROUPS       = 'groups';

    /** @var string */
    protected $fieldNameBuilder;

    /** @var ProductBuilder */
    protected $productBuilder;

    /** @var string */
    protected $associationClass;

    /** @var string */
    protected $categoryClass;

    /** @var string */
    protected $familyClass;

    /** @var string */
    protected $groupClass;

    /** @var string */
    protected $productValueClass;

    /**
     * @param ManagerRegistry  $managerRegistry
     * @param string           $entityClass
     * @param ProductBuilder   $productBuilder
     * @param FieldNameBuilder $fieldNameBuilder
     * @param string           $associationClass
     * @param string           $categoryClass
     * @param string           $familyClass
     * @param string           $groupClass
     * @param string           $productValueClass
     */
    public function __construct(
        ManagerRegistry $managerRegistry,
        $entityClass,
        ProductBuilder $productBuilder,
        FieldNameBuilder $fieldNameBuilder,
        $associationClass,
        $categoryClass,
        $familyClass,
        $groupClass,
        $productValueClass
    ) {
        parent::__construct($managerRegistry, $entityClass);

        $this->productBuilder    = $productBuilder;
        $this->fieldNameBuilder  = $fieldNameBuilder;

        $this->associationClass  = $associationClass;
        $this->categoryClass     = $categoryClass;
        $this->familyClass       = $familyClass;
        $this->groupClass        = $groupClass;
        $this->productValueClass = $productValueClass;
    }

    /**
     * {@inheritdoc}
     */
    protected function doDenormalize($data, $format, array $context)
    {
        /** @var ProductInterface $product */
        $product = $this->getEntity($data, $context);

        if (isset($data[self::FIELD_ENABLED])) {
            $product->setEnabled((bool) $data[self::FIELD_ENABLED]);
            unset($data[self::FIELD_ENABLED]);
        }

        if (isset($data[self::FIELD_FAMILY])) {
            $this->denormalizeFamily($data[self::FIELD_FAMILY], $format, $context, $product);
            unset($data[self::FIELD_FAMILY]);
        }

        if (isset($data[self::FIELD_CATEGORIES])) {
            $this->denormalizeCategories($data[self::FIELD_CATEGORIES], $format, $context, $product);
            unset($data[self::FIELD_CATEGORIES]);
        }

        if (isset($data[self::FIELD_GROUPS])) {
            $this->denormalizeGroups($data[self::FIELD_GROUPS], $format, $context, $product);
            unset($data[self::FIELD_GROUPS]);
        }

        $this->denormalizeAssociations($data, $format, $context, $product);

        $this->denormalizeValues($data, $format, $context, $product);

        return $product;
    }

    /**
     * Denormalize the product family
     *
     * @param string           $data
     * @param string           $format
     * @param array            $context
     * @param ProductInterface $product
     */
    protected function denormalizeFamily($data, $format, array $context, ProductInterface $product)
    {
        if (strlen($data) > 0) {
            $family = $this->serializer->denormalize($data, $this->familyClass, $format, $context);
        } else {
            $family = null;
        }

        $product->setFamily($family);
    }

    /**
     * Denormalize product categories
     *
     * @param string           $data
     * @param string           $format
     * @param array            $context
     * @param ProductInterface $product
     */
    protected function denormalizeCategories($data, $format, array $context, ProductInterface $product)
    {
        foreach ($product->getCategories() as $category) {
            $product->removeCategory($category);
        }

        $categoryCodes = strlen($data) > 0 ? explode(",", $data) : array();
        foreach ($categoryCodes as $categoryCode) {
            $product->addCategory(
                $this->serializer->denormalize($categoryCode, $this->categoryClass, $format, $context)
            );
        }
    }

    /**
     * Denormalize product groups
     *
     * @param string           $data
     * @param string           $format
     * @param array            $context
     * @param ProductInterface $product
     */
    protected function denormalizeGroups($data, $format, array $context, ProductInterface $product)
    {
        foreach ($product->getGroups() as $group) {
            $product->removeGroup($group);
        }

        $groupCodes = strlen($data) > 0 ? explode(",", $data) : array();
        foreach ($groupCodes as $groupCode) {
            $product->addGroup(
                $this->serializer->denormalize($groupCode, $this->groupClass, $format, $context)
            );
        }
    }

    /**
     * Denormalize product associations
     *
     * @param string           &$data
     * @param string           $format
     * @param array            $context
     * @param ProductInterface $product
     *
     * @throws RevertException
     */
    protected function denormalizeAssociations(&$data, $format, array $context, ProductInterface $product)
    {
        foreach ($product->getAssociations() as $association) {
            foreach ($association->getGroups() as $group) {
                $association->removeGroup($group);
            }

            foreach ($association->getProducts() as $prod) {
                $association->removeProduct($prod);
            }
        }

        // Get association field names and add associations
        $assocFieldNames  = $this->fieldNameBuilder->getAssociationFieldNames();
        foreach ($assocFieldNames as $assocFieldName) {
            if (isset($data[$assocFieldName])) {
                if (strlen($data[$assocFieldName]) > 0) {
                    list($associationTypeCode, $part) = explode('-', $assocFieldName);

                    $association = $product->getAssociationForTypeCode($associationTypeCode);
                    $association = $this->serializer->denormalize(
                        $data[$assocFieldName],
                        $this->associationClass,
                        $format,
                        [
                            'entity'                => $association,
                            'association_type_code' => $associationTypeCode,
                            'part'                  => $part
                        ] + $context
                    );

                    if (!$product->getAssociationForTypeCode($associationTypeCode)) {
                        $product->addAssociation($association);
                    }
                }

                unset($data[$assocFieldName]);
            }
        }

        foreach (array_keys($data) as $fieldName) {
            if (null !== $matches = $this->fieldNameBuilder->extractAssociationFieldNameInfos($fieldName)) {
                throw new RevertException(
                    sprintf('Association type "%s" does not exist anymore', $matches['assoc_type_code'])
                );
            }
        }
    }

    /**
     * Denormalize product values
     *
     * @param string           $data
     * @param string           $format
     * @param array            $context
     * @param ProductInterface $product
     */
    protected function denormalizeValues($data, $format, array $context, ProductInterface $product)
    {
        foreach ($product->getValues() as $value) {
            $product->removeValue($value);
        }

        foreach ($data as $attFieldName => $dataValue) {
            $attributeInfos = $this->fieldNameBuilder->extractAttributeFieldNameInfos($attFieldName);
            $attribute = $attributeInfos['attribute'];
            unset($attributeInfos['attribute']);

            if (!$product->hasAttribute($attribute)) {
                $this->productBuilder->addAttributeToProduct($product, $attribute);
            }

            // Denormalize data value.
            // The value is already added to the product so automatically updated
            $productValue = $product->getValue(
                $attribute->getCode(),
                $attributeInfos['locale_code'],
                $attributeInfos['scope_code']
            );
            $this->serializer->denormalize(
                $dataValue,
                $this->productValueClass,
                $format,
                [
                    'product' => $product,
                    'entity'  => $productValue
                ] + $attributeInfos + $context
            );
        }
    }
}
