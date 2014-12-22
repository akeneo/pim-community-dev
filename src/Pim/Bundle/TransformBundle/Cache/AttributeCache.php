<?php

namespace Pim\Bundle\TransformBundle\Cache;

use Pim\Bundle\CatalogBundle\Model\FamilyInterface;
use Pim\Bundle\CatalogBundle\Model\GroupInterface;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * Caches the attributes of an import. Do not forget to call the reset method between two imports.
 *
 * @author    Antoine Guigan <antoine@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AttributeCache
{
    /**
     * @var RegistryInterface
     */
    protected $doctrine;

    /**
     * @var array
     */
    protected $familyAttributeCodes = array();

    /***
     * @var array
     */
    protected $groupAttributeCodes = array();

    /**
     * @var string
     */
    protected $attributeClass;

    /**
     * Constructor
     * @param RegistryInterface $doctrine
     * @param string            $attributeClass
     */
    public function __construct(RegistryInterface $doctrine, $attributeClass)
    {
        $this->doctrine = $doctrine;
        $this->attributeClass = $attributeClass;
    }

    /**
     * Sets the attributes and identifierAttributes properties
     *
     * @param array $columnsInfo
     *
     * @return array|null
     */
    public function getAttributes($columnsInfo)
    {
        if (!count($columnsInfo)) {
            return array();
        }
        $codes = array_unique(
            array_map(
                function ($columnInfo) {
                    return $columnInfo->getName();
                },
                $columnsInfo
            )
        );

        $attributes = $this->doctrine->getRepository($this->attributeClass)
                ->findBy(array('code' => $codes));
        $attributeMap = array();
        foreach ($attributes as $attribute) {
            $attributeMap[$attribute->getCode()] = $attribute;
        }

        return $attributeMap;
    }

    /**
     * Returns the required attribute codes for a product
     *
     * @param ProductInterface $product
     *
     * @return array
     */
    public function getRequiredAttributeCodes(ProductInterface $product)
    {
        $codes = array();

        if ($product->getFamily()) {
            $codes = $this->getFamilyAttributeCodes($product->getFamily());
        }

        foreach ($product->getGroups() as $group) {
            $codes = array_merge($codes, $this->getGroupAttributeCodes($group));
        }

        if ($product->getId()) {
            foreach ($product->getValues() as $value) {
                $codes[] = $value->getAttribute()->getCode();
            }
        }

        return array_unique($codes);
    }

    /**
     * Returns the attribute codes for a group
     *
     * @param GroupInterface $group
     *
     * @return array
     */
    protected function getGroupAttributeCodes(GroupInterface $group)
    {
        $code = $group->getCode();
        if (!isset($this->groupAttributeCodes[$code])) {
            $this->groupAttributeCodes[$code] = $this->getAttributeCodes($group);
        }

        return $this->groupAttributeCodes[$code];
    }

    /**
     * Returns the attribute codes for a family
     *
     * @param FamilyInterface $family
     *
     * @return array
     */
    protected function getFamilyAttributeCodes(FamilyInterface $family)
    {
        $code = $family->getCode();
        if (!isset($this->familyAttributeCodes[$code])) {
            $this->familyAttributeCodes[$code] = $this->getAttributeCodes($family);
        }

        return $this->familyAttributeCodes[$code];
    }

    /**
     * Returns the attribute codes for an object
     *
     * @param object $object
     *
     * @return array
     */
    protected function getAttributeCodes($object)
    {
        return array_map(
            function ($attribute) {
                return $attribute->getCode();
            },
            $object->getAttributes()->toArray()
        );
    }
}
