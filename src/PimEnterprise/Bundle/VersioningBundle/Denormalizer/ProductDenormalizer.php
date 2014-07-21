<?php

namespace PimEnterprise\Bundle\VersioningBundle\Denormalizer;

use Pim\Bundle\CatalogBundle\Model\ProductInterface;

/**
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class ProductDenormalizer extends AbstractEntityDenormalizer
{
    const FIELD_ENABLED = 'enabled';
    const FIELD_FAMILY = 'family';
    const FIELD_CATEGORIES = 'categories';
    const FIELD_GROUPS = 'groups';
    const FIELD_ASSOCIATIONS = 'associations';
    const FIELD_VALUES = 'values';

    /**
     * {@inheritdoc}
     */
    public function denormalize($data, $class, $format = null, array $context = array())
    {
        $product = $this->getEntity($data['sku']); //TODO Remove hardcoded stuff
        var_dump($data); echo "<br />";
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

        if (isset($data[self::FIELD_ASSOCIATIONS])) {
            $this->denormalizeAssociations($data[self::FIELD_ASSOCIATIONS], $product);

        }


        $this->printProduct($product);

        return $product;
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

    /**
     * @param string $data
     * @param ProductInterface $product
     */
    protected function denormalizeFamily($data, ProductInterface $product)
    {
        if (strlen($data) > 0) {
            $family = $this->serializer->denormalize($data, 'Pim\Bundle\CatalogBundle\Entity\Family');
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
                $this->serializer->denormalize($categoryCode, 'Pim\Bundle\CatalogBundle\Entity\Category')
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
            echo "GROUP CODE: ". $groupCode ."<br />";

            $group = $this->serializer->denormalize($groupCode, 'Pim\Bundle\CatalogBundle\Entity\Group');
            var_dump(get_class($group));

            $product->addGroup(
                $this->serializer->denormalize($groupCode, 'Pim\Bundle\CatalogBundle\Entity\Group')
            );
        }
    }

    /**
     * @param string           $data
     * @param ProductInterface $product
     */
    protected function denormalizeAssociations($data, ProductInterface $product)
    {
        // Remove existing associations
        foreach ($product->getAssociations() as $association) {
            $product->removeAssociation($association);
        }

        // Adding associations
//        $associationCodes = explode(",", $data);
//        foreach ($associationCodes as $associationCode) {
//            $product->addAssociation(
//                $this->serializer->denormalize($associationCode, 'Pim\Bundle\CatalogBundle\Model\Association')
//            );
//        }
    }
}
