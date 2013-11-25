<?php

namespace Pim\Bundle\ImportExportBundle\Cache;

use Doctrine\ORM\EntityRepository;
use Pim\Bundle\CatalogBundle\Entity\Family;
use Pim\Bundle\CatalogBundle\Entity\Group;
use Pim\Bundle\CatalogBundle\Entity\ProductAttribute;
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
     * @var string
     */
    protected $attributeClass;

    /**
     * @var array
     */
    protected $attributes;

    /**
     * @var ProductAttribute
     */
    protected $identifierAttribute;

    /**
     * @var array
     */
    protected $familyAttributeCodes = array();

    /***
     * @var array
     */
    protected $groupAttributeCodes = array();

    /**
     * Constructor
     *
     * @param RegistryInterface $doctrine
     * @param string            $attributeClass
     */
    public function __construct(RegistryInterface $doctrine, $attributeClass)
    {
        $this->doctrine = $doctrine;
        $this->attributeClass = $attributeClass;
    }

    public function getAttributes(array $codes) {
        return $this->getRepository()->findBy(array('code' => $codes));
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
     * @param Group $group
     *
     * @return array
     */
    protected function getGroupAttributeCodes(Group $group)
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
     * @param Family $family
     *
     * @return array
     */
    protected function getFamilyAttributeCodes(Family $family)
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

    /**
     * @return EntityRepository
     */
    protected function getRepository()
    {
        return $this->doctrine->getRepository($this->attributeClass);
    }
}
