<?php

namespace Pim\Bundle\EnrichBundle\Filter;

use Oro\Bundle\SecurityBundle\SecurityFacade;
use Pim\Bundle\CatalogBundle\Exception\ObjectNotFoundException;
use Pim\Bundle\CatalogBundle\Filter\CollectionFilterInterface;
use Pim\Bundle\CatalogBundle\Filter\ObjectFilterInterface;
use Pim\Bundle\CatalogBundle\Manager\LocaleManager;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Model\LocaleInterface;
use Pim\Bundle\CatalogBundle\Repository\AttributeRepositoryInterface;

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

    /** @var LocaleManager */
    protected $localeManager;

    /** @var array */
    protected $attributes = [];

    /** @var array */
    protected $locales = [];

    /**
     * @param SecurityFacade               $securityFacade
     * @param ObjectFilterInterface        $objectFilter
     * @param AttributeRepositoryInterface $attributeRepository
     * @param LocaleManager                $localeManager
     */
    public function __construct(
        SecurityFacade $securityFacade,
        ObjectFilterInterface $objectFilter,
        AttributeRepositoryInterface $attributeRepository,
        LocaleManager $localeManager
    ) {
        $this->securityFacade      = $securityFacade;
        $this->objectFilter        = $objectFilter;
        $this->attributeRepository = $attributeRepository;
        $this->localeManager       = $localeManager;
    }

    /**
     * Filter product data according to edit permissions
     *
     * {@inheritdoc}
     */
    public function filterCollection($collection, $type, array $options = [])
    {
        $this->attributes = $this->attributeRepository->getAttributesAsArray();
        $this->locales    = $this->localeManager->getActiveLocales();

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
     *
     * @throws ObjectNotFoundException
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
     * @return AttributeInterface
     *
     * @throws ObjectNotFoundException
     */
    protected function getAttribute($code)
    {
        if (!array_key_exists($code, $this->attributes)) {
            throw new ObjectNotFoundException(sprintf('Attribute with code "%s" was not found.', $code));
        }

        return $this->attributes[$code];
    }

    /**
     * @param string $code
     *
     * @return LocaleInterface
     *
     * @throws ObjectNotFoundException
     */
    protected function getLocale($code)
    {
        if (!array_key_exists($code, $this->locales)) {
            throw new ObjectNotFoundException(sprintf('Locale with code "%s" was not found.', $code));
        }

        return $this->locales[$code];
    }
}
