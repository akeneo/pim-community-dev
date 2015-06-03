<?php

namespace Pim\Bundle\EnrichBundle\Filter;

use Oro\Bundle\SecurityBundle\SecurityFacade;
use Pim\Bundle\CatalogBundle\Exception\ObjectNotFoundException;
use Pim\Bundle\CatalogBundle\Filter\CollectionFilterInterface;
use Pim\Bundle\CatalogBundle\Filter\ObjectFilterInterface;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Model\LocaleInterface;
use Pim\Bundle\CatalogBundle\Repository\AttributeRepositoryInterface;
use Pim\Bundle\CatalogBundle\Repository\LocaleRepositoryInterface;

/**
 * Product edit data filter
 *
 * @author    Yohan Blain <yohan.blain@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductEditDataFilter implements CollectionFilterInterface
{
    /** @var SecurityFacade */
    protected $securityFacade;

    /** @var ObjectFilterInterface */
    protected $objectFilter;

    /** @var AttributeRepositoryInterface */
    protected $attributeRepository;

    /** @var LocaleRepositoryInterface */
    protected $localeRepository;

    /** @var AttributeInterface[] */
    protected $attributes = [];

    /** @var LocaleInterface[] */
    protected $locales = [];

    /**
     * @param SecurityFacade               $securityFacade
     * @param ObjectFilterInterface        $objectFilter
     * @param AttributeRepositoryInterface $attributeRepository
     */
    public function __construct(
        SecurityFacade $securityFacade,
        ObjectFilterInterface $objectFilter,
        AttributeRepositoryInterface $attributeRepository,
        LocaleRepositoryInterface $localeRepository
    ) {
        $this->securityFacade      = $securityFacade;
        $this->objectFilter        = $objectFilter;
        $this->attributeRepository = $attributeRepository;
        $this->localeRepository    = $localeRepository;
    }

    /**
     * Filter product data according to edit permissions
     *
     * {@inheritdoc}
     */
    public function filterCollection($collection, $type, array $options = [])
    {
        $filteredProductData = [];

        foreach ($collection as $type => $data) {
            if ('values' === $type) {
                $filteredProductData['values'] = $this->filterValuesData($data);
            } else {
                switch ($type) {
                    case 'family':
                        $acl = 'pim_enrich_product_change_family';
                        break;
                    case 'groups':
                        $acl = 'pim_enrich_product_add_to_groups';
                        break;
                    case 'categories':
                        $acl = 'pim_enrich_product_categories_view';
                        break;
                    case 'enabled':
                        $acl = 'pim_enrich_product_change_state';
                        break;
                    case 'associations':
                        $acl = 'pim_enrich_associations_view';
                        break;
                    default:
                        $acl = null;
                }

                if (null === $acl || $this->securityFacade->isGranted($acl)) {
                    $filteredProductData[$type] = $data;
                }
            }
        }

        return $filteredProductData;
    }

    /**
     * @param array $valuesData
     *
     * @return array
     */
    protected function filterValuesData($valuesData)
    {
        $filteredValuesData = [];

        foreach ($valuesData as $attributeCode => $contextValues) {
            $attribute = $this->getAttribute($attributeCode);
            if (!$this->objectFilter->filterObject($attribute, 'pim:internal_api:attribute:edit')) {
                $filteredContextValues = [];

                foreach ($contextValues as $contextValue) {
                    if (null === $contextValue['locale'] ||
                        !$this->objectFilter->filterObject(
                            $this->getLocale($contextValue['locale']),
                            'pim:internal_api:locale:edit'
                        )
                    ) {
                        $filteredContextValues[] = $contextValue;
                    }
                }

                $filteredValuesData[$attributeCode] = $filteredContextValues;
            }
        }

        return array_filter($filteredValuesData);
    }

    /**
     * {@inheritdoc}
     */
    public function supportsCollection($collection, $type, array $options = [])
    {
        return false;
    }

    /**
     * @param string $code
     *
     * @throws ObjectNotFoundException
     *
     * @return AttributeInterface
     */
    protected function getAttribute($code)
    {
        if (!array_key_exists($code, $this->attributes)) {
            $attribute = $this->attributeRepository->findOneByIdentifier($code);
            if (!$attribute) {
                throw new ObjectNotFoundException(sprintf('Attribute with code "%s" was not found.', $code));
            }

            $this->attributes[$code] = $attribute;
        }

        return $this->attributes[$code];
    }

    /**
     * @param string $code
     * @param bool   $activeOnly
     *
     * @throws ObjectNotFoundException
     *
     * @return LocaleInterface
     */
    protected function getLocale($code, $activeOnly = true)
    {
        if (!array_key_exists($code, $this->locales)) {
            $locale = $this->localeRepository->findOneByIdentifier($code);
            if (!$locale) {
                throw new ObjectNotFoundException(sprintf('Locale with code "%s" was not found.', $code));
            }
            if ($activeOnly && !$locale->isActivated()) {
                throw new ObjectNotFoundException(sprintf('Active locale with code "%s" was not found.', $code));
            }

            $this->locales[$code] = $locale;
        }

        return $this->locales[$code];
    }
}
