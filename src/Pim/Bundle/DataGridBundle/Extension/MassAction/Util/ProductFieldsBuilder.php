<?php

namespace Pim\Bundle\DataGridBundle\Extension\MassAction\Util;

use Pim\Bundle\CatalogBundle\Context\CatalogContext;
use Pim\Component\Catalog\AttributeTypes;
use Pim\Component\Catalog\Normalizer\Standard\Product\PropertiesNormalizer;
use Pim\Component\Catalog\Repository\AssociationTypeRepositoryInterface;
use Pim\Component\Catalog\Repository\AttributeRepositoryInterface;
use Pim\Component\Catalog\Repository\CurrencyRepositoryInterface;
use Pim\Component\Catalog\Repository\LocaleRepositoryInterface;
use Pim\Component\Catalog\Repository\ProductRepositoryInterface;

/**
 * Fields builder, allows to prepare the field list for a flat file export, should be part of normalizer at some point
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductFieldsBuilder
{
    /** @var ProductRepositoryInterface */
    protected $productRepository;

    /** @var AttributeRepositoryInterface */
    protected $attributeRepository;

    /** @var LocaleRepositoryInterface */
    protected $localeRepository;

    /** @var CurrencyRepositoryInterface */
    protected $currencyRepository;

    /** @var AssociationTypeRepositoryInterface */
    protected $assocTypeRepo;

    /** @var CatalogContext */
    protected $catalogContext;

    /** @var integer(] */
    protected $attributeIds;

    /**
     * Constructor
     *
     * @param ProductRepositoryInterface         $productRepository
     * @param AttributeRepositoryInterface       $attributeRepository
     * @param LocaleRepositoryInterface          $localeRepository
     * @param CurrencyRepositoryInterface        $currencyRepository
     * @param AssociationTypeRepositoryInterface $assocTypeRepo
     * @param CatalogContext                     $catalogContext
     */
    public function __construct(
        ProductRepositoryInterface $productRepository,
        AttributeRepositoryInterface $attributeRepository,
        LocaleRepositoryInterface $localeRepository,
        CurrencyRepositoryInterface $currencyRepository,
        AssociationTypeRepositoryInterface $assocTypeRepo,
        CatalogContext $catalogContext
    ) {
        $this->productRepository = $productRepository;
        $this->attributeRepository = $attributeRepository;
        $this->localeRepository = $localeRepository;
        $this->currencyRepository = $currencyRepository;
        $this->assocTypeRepo = $assocTypeRepo;
        $this->catalogContext = $catalogContext;
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

        $attributes = $this->attributeRepository->findBy(['id' => $this->getAttributeIds()]);

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
        $this->attributeIds = $this->productRepository
            ->getAvailableAttributeIdsToExport($productIds);
    }

    /**
     * Prepare fields list for CSV headers
     *
     * @param array $attributesList
     *
     * @return array
     */
    protected function prepareFieldsList(array $attributesList = [])
    {
        $fieldsList = $this->prepareAttributesList($attributesList);
        $fieldsList[] = PropertiesNormalizer::FIELD_FAMILY;
        $fieldsList[] = PropertiesNormalizer::FIELD_CATEGORIES;
        $fieldsList[] = PropertiesNormalizer::FIELD_GROUPS;

        $associationTypes = $this->assocTypeRepo->findAll();
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
        $scopeCode = $this->catalogContext->getScopeCode();
        $localeCodes = $this->localeRepository->getActivatedLocaleCodes();
        $fieldsList = [];

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
                foreach ($this->currencyRepository->getActivatedCurrencyCodes() as $currencyCode) {
                    $fieldsList[] = sprintf('%s-%s', $attCode, $currencyCode);
                }
            } else {
                $fieldsList[] = $attCode;
            }
        }

        return $fieldsList;
    }
}
