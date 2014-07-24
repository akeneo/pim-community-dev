<?php

namespace Pim\Bundle\TransformBundle\Builder;

use Doctrine\Common\Persistence\ManagerRegistry;
use Pim\Bundle\CatalogBundle\Model\AbstractAttribute;

/**
 * Create field names for associations and product values
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FieldNameBuilder
{
    /** @var ManagerRegistry */
    protected $managerRegistry;

    /** @var string */
    protected $assocTypeClass;

    /**
     * @param ManagerRegistry $managerRegistry
     * @param string          $assocTypeClass
     * @param string          $attributeClass
     */
    public function __construct(ManagerRegistry $managerRegistry, $assocTypeClass, $attributeClass)
    {
        $this->managerRegistry = $managerRegistry;
        $this->assocTypeClass  = $assocTypeClass;
        $this->attributeClass  = $attributeClass;
    }

    /**
     * Get the association field names
     *
     * @return array
     */
    public function getAssociationFieldNames()
    {
        $fieldNames = [];
        foreach ($this->getAssociationTypes() as $assocType) {
            $fieldNames[] = $assocType->getCode() .'-groups';
            $fieldNames[] = $assocType->getCode() .'-products';
        }

        return $fieldNames;
    }

    /**
     * @param $fieldName
     *
     * @return array
     */
    public function extractAttributeFieldNameInfos($fieldName)
    {
        $explodedFieldName = explode("-", $fieldName);
        $attributeCode = $explodedFieldName[0];

        $attribute = $this->getRepository($this->attributeClass)->findByReference($attributeCode);

        return $this->extractAttributeInfos($attribute, $explodedFieldName);
    }

    protected function extractAttributeInfos(AbstractAttribute $attribute, array $explodedFieldName)
    {
        if ($attribute->isLocalizable() && $attribute->isScopable()) {
            $localeCode = $explodedFieldName[1];
            $scopeCode  = $explodedFieldName[2];
            $priceCurrency = $attribute->getBackendType() === 'prices' ? $explodedFieldName[3] : null;
        } elseif ($attribute->isLocalizable()) {
            $localeCode = $explodedFieldName[1];
            $scopeCode  = null;
            $priceCurrency = $attribute->getBackendType() === 'prices' ? $explodedFieldName[2] : null;
        } elseif ($attribute->isScopable()) {
            $localeCode = null;
            $scopeCode  = $explodedFieldName[1];
            $priceCurrency = $attribute->getBackendType() === 'prices' ? $explodedFieldName[2] : null;
        } else {
            $localeCode = null;
            $scopeCode  = null;
            $priceCurrency = $attribute->getBackendType() === 'prices' ? $explodedFieldName[1] : null;
        }

        $priceArray = (null === $priceCurrency) ? [] : ['price_currency' => $priceCurrency];

        return [
            'attribute'   => $attribute,
            'locale_code' => $localeCode,
            'scope_code'  => $scopeCode,
        ] + $priceArray;
    }

    /**
     * @param string $entityClass
     *
     * @return \Doctrine\Common\Persistence\ObjectRepository
     */
    protected function getRepository($entityClass)
    {
        return $this->managerRegistry->getRepository($entityClass);
    }

    /**
     * @return AssociationType[]
     */
    protected function getAssociationTypes()
    {
        return $this->getRepository($this->assocTypeClass)->findAll();
    }
}
