<?php

namespace Pim\Bundle\DataGridBundle\Extension\MassAction\Util;

use Pim\Bundle\CatalogBundle\AttributeType\AttributeTypes;
use Pim\Bundle\CatalogBundle\Context\CatalogContext;
use Pim\Bundle\CatalogBundle\Manager\AssociationTypeManager;
use Pim\Bundle\CatalogBundle\Manager\CurrencyManager;
use Pim\Bundle\CatalogBundle\Manager\LocaleManager;
use Pim\Bundle\CatalogBundle\Manager\ProductManagerInterface;
use Pim\Bundle\TransformBundle\Normalizer\Flat\ProductNormalizer;

/**
 * Fields builder, allows to prepare the field list for a flat file export, should be part of normalizer at some point
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductFieldsBuilder
{
    /** @var ProductManagerInterface $productManager */
    protected $productManager;

    /** @var LocaleManager $localeManager */
    protected $localeManager;

    /** @var CurrencyManager $currencyManager */
    protected $currencyManager;

    /** @var AssociationTypeManager $assocTypeManager */
    protected $assocTypeManager;

    /** @var CatalogContext $catalogContext */
    protected $catalogContext;

    /** @var integer(] */
    protected $attributeIds;

    /**
     * Constructor
     *
     * @param ProductManagerInterface $productManager
     * @param LocaleManager           $localeManager
     * @param CurrencyManager         $currencyManager
     * @param AssociationTypeManager  $assocTypeManager
     * @param CatalogContext          $catalogContext
     */
    public function __construct(
        ProductManagerInterface $productManager,
        LocaleManager $localeManager,
        CurrencyManager $currencyManager,
        AssociationTypeManager $assocTypeManager,
        CatalogContext $catalogContext
    ) {
        $this->productManager   = $productManager;
        $this->localeManager    = $localeManager;
        $this->currencyManager  = $currencyManager;
        $this->assocTypeManager = $assocTypeManager;
        $this->catalogContext   = $catalogContext;
    }

    /**
     * Get fields for products
     *
     * @param array $productIds
     *
     * @return array
     */
    public function getFieldsList($productIds)
    {
        $this->prepareAvailableAttributeIds($productIds);

        $attributes = $this->getAttributeIds();

        if (empty($attributes)) {
            return [];
        }

        $attributes = $this->productManager->getAttributeRepository()->findBy(array('id' => $this->getAttributeIds()));

        return $this->prepareFieldsList($attributes);
    }

    /**
     * Get attribute ids
     *
     * @return integer[]
     */
    public function getAttributeIds()
    {
        return $this->attributeIds;
    }

    /**
     * Prepare available attribute ids
     *
     * @param array $productIds
     */
    protected function prepareAvailableAttributeIds($productIds)
    {
        $this->attributeIds = $this->productManager
            ->getProductRepository()
            ->getAvailableAttributeIdsToExport($productIds);
    }

    /**
     * Prepare fields list for CSV headers
     *
     * @param array $attributesList
     *
     * @return array
     */
    protected function prepareFieldsList(array $attributesList = array())
    {
        $fieldsList   = $this->prepareAttributesList($attributesList);
        $fieldsList[] = ProductNormalizer::FIELD_FAMILY;
        $fieldsList[] = ProductNormalizer::FIELD_CATEGORY;
        $fieldsList[] = ProductNormalizer::FIELD_GROUPS;

        $associationTypes = $this->assocTypeManager->getAssociationTypes();
        foreach ($associationTypes as $associationType) {
            $fieldsList[] = sprintf('%s-groups', $associationType->getCode());
            $fieldsList[] = sprintf('%s-products', $associationType->getCode());
        }

        return $fieldsList;
    }

    /**
     * Prepare attributes list for CSV headers
     *
     * @param array $attributesList
     *
     * @return array
     */
    protected function prepareAttributesList(array $attributesList)
    {
        $scopeCode   = $this->catalogContext->getScopeCode();
        $localeCodes = $this->localeManager->getActiveCodes();
        $fieldsList  = [];

        foreach ($attributesList as $attribute) {
            $attCode = $attribute->getCode();
            if ($attribute->isLocalizable() && $attribute->isScopable()) {
                foreach ($localeCodes as $localeCode) {
                    $fieldsList[] = sprintf('%s-%s-%s', $attCode, $localeCode, $scopeCode);
                }
            } elseif ($attribute->isLocalizable()) {
                foreach ($localeCodes as $localeCode) {
                    $fieldsList[] = sprintf('%s-%s', $attCode, $localeCode);
                }
            } elseif ($attribute->isScopable()) {
                $fieldsList[] = sprintf('%s-%s', $attCode, $scopeCode);
            } elseif (AttributeTypes::IDENTIFIER === $attribute->getAttributeType()) {
                array_unshift($fieldsList, $attCode);
            } elseif (AttributeTypes::PRICE_COLLECTION === $attribute->getAttributeType()) {
                foreach ($this->currencyManager->getActiveCodes() as $currencyCode) {
                    $fieldsList[] = sprintf('%s-%s', $attCode, $currencyCode);
                }
            } else {
                $fieldsList[] = $attCode;
            }
        }

        return $fieldsList;
    }
}
