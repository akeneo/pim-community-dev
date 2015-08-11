<?php

namespace Pim\Bundle\EnrichBundle\Filter;

use Oro\Bundle\SecurityBundle\SecurityFacade;
use Pim\Bundle\CatalogBundle\Exception\ObjectNotFoundException;
use Pim\Bundle\CatalogBundle\Filter\CollectionFilterInterface;
use Pim\Bundle\CatalogBundle\Filter\ObjectFilterInterface;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Model\LocaleInterface;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
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

    /** @var array */
    protected $acls = [
        'family'       => 'pim_enrich_product_change_family',
        'groups'       => 'pim_enrich_product_add_to_groups',
        'categories'   => 'pim_enrich_product_categories_view',
        'enabled'      => 'pim_enrich_product_change_state',
        'associations' => 'pim_enrich_associations_view'
    ];

    /**
     * @param SecurityFacade               $securityFacade
     * @param ObjectFilterInterface        $objectFilter
     * @param AttributeRepositoryInterface $attributeRepository
     * @param LocaleRepositoryInterface    $localeRepository
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
        $newProductData = [];
        $allowedToClassify = $this->isAllowedToClassify($options['product']);

        foreach ($collection as $type => $data) {
            $acl = $this->getAclForType($type);
            $actionGranted = null === $acl || $this->securityFacade->isGranted($acl);

            if ($actionGranted) {
                $newProductData[$type] = $data;
            }

            if ('values' === $type) {
                $newProductData['values'] = $this->filterValuesData($data);
            } elseif ('categories' === $type && !$allowedToClassify) {
                unset($newProductData['categories']);
            }
        }

        return $newProductData;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsCollection($collection, $type, array $options = [])
    {
        return false;
    }

    /**
     * @param array $valuesData
     *
     * @return array
     */
    protected function filterValuesData(array $valuesData)
    {
        $newValuesData = [];

        foreach ($valuesData as $attributeCode => $contextValues) {
            $attribute = $this->getAttribute($attributeCode);
            if (!$this->objectFilter->filterObject($attribute, 'pim.internal_api.attribute.edit')) {
                $newContextValues = [];

                foreach ($contextValues as $contextValue) {
                    if (null === $contextValue['locale'] ||
                        !$this->objectFilter->filterObject(
                            $this->getLocale($contextValue['locale']),
                            'pim.internal_api.locale.edit'
                        )
                    ) {
                        $newContextValues[] = $contextValue;
                    }
                }

                $newValuesData[$attributeCode] = $newContextValues;
            }
        }

        return array_filter($newValuesData);
    }

    /**
     * @param ProductInterface $product
     *
     * @return bool
     */
    protected function isAllowedToClassify(ProductInterface $product)
    {
        return true;
    }

    /**
     * Return which ACL should be used to filter data of specified type.
     *
     * @param string
     *
     * @return string|null
     */
    protected function getAclForType($type)
    {
        return isset($this->acls[$type]) ? $this->acls[$type] : null;
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
