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
use Pim\Bundle\CatalogBundle\Repository\ChannelRepositoryInterface;
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

    /** @var ChannelRepositoryInterface */
    protected $channelRepository;

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
     * @param ChannelRepositoryInterface   $channelRepository
     */
    public function __construct(
        SecurityFacade $securityFacade,
        ObjectFilterInterface $objectFilter,
        AttributeRepositoryInterface $attributeRepository,
        LocaleRepositoryInterface $localeRepository,
        ChannelRepositoryInterface $channelRepository
    ) {
        $this->securityFacade      = $securityFacade;
        $this->objectFilter        = $objectFilter;
        $this->attributeRepository = $attributeRepository;
        $this->localeRepository    = $localeRepository;
        $this->channelRepository   = $channelRepository;
    }

    /**
     * Filter product data according to edit permissions
     *
     * {@inheritdoc}
     */
    public function filterCollection($collection, $type, array $options = [])
    {
        $newProductData = [];
        $product = $options['product'];

        foreach ($collection as $type => $data) {
            if ($this->isAllowed($product, $type)) {
                $newProductData[$type] = $this->filterData($type, $data);
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
     * Filter & return the given $data for the given $type
     *
     * @param string $type
     * @param mixed  $data
     *
     * @return mixed
     */
    protected function filterData($type, $data)
    {
        if ('values' === $type) {
            $data = $this->filterValuesData($data);
        }

        return $data;
    }

    /**
     * @param array $valuesData
     *
     * @return array
     */
    protected function filterValuesData(array $valuesData)
    {
        $newValuesData = [];

        foreach ($valuesData as $attributeCode => $values) {
            $attribute = $this->getAttribute($attributeCode);
            if (!$this->objectFilter->filterObject($attribute, 'pim.internal_api.attribute.edit')) {
                $newValuesData[$attributeCode] = $this->getNewValuesData($attribute, $values);
            }
        }

        return array_filter($newValuesData);
    }

    /**
     * @param AttributeInterface $attribute
     * @param array              $values
     *
     * @throws ObjectNotFoundException
     *
     * @return array
     */
    protected function getNewValuesData(AttributeInterface $attribute, array $values)
    {
        $newValues = [];

        foreach ($values as $value) {
            $acceptValue = true;

            if (null !== $value['locale']) {
                $isAuthorizedOnLocale = !$this->objectFilter->filterObject(
                    $this->getLocale($value['locale']),
                    'pim.internal_api.locale.edit'
                );

                $isEditableOnLocale = $attribute->isLocaleSpecific() ?
                    in_array($value['locale'], $attribute->getLocaleSpecificCodes()) :
                    true
                ;

                $acceptValue = $isAuthorizedOnLocale && $isEditableOnLocale;
            }

            if ($attribute->isScopable() && $acceptValue) {
                $channel = $this->channelRepository->findOneByIdentifier($value['scope']);
                if (null === $channel) {
                    $acceptValue = false;
                }
            }

            if ($acceptValue) {
                $newValues[] = $value;
            }
        }

        return $newValues;
    }

    /**
     * Return whether the current user is allowed to update the given modification $type
     * on the given $product
     *
     * @param ProductInterface $product
     * @param string           $type
     *
     * @return bool
     */
    protected function isAllowed(ProductInterface $product, $type)
    {
        $isAllowed = true;

        switch ($type) {
            case 'family':
                $isAllowed = $this->isAllowedToUpdateFamily($product);
                break;
            case 'groups':
                $isAllowed = $this->isAllowedToUpdateGroups($product);
                break;
            case 'categories':
                $isAllowed = $this->isAllowedToClassify($product);
                break;
            case 'enabled':
                $isAllowed = $this->isAllowedToUpdateStatus($product);
                break;
            case 'associations':
                $isAllowed = $this->isAllowedToUpdateAssociations($product);
                break;
            case 'values':
                $isAllowed = $this->isAllowedToUpdateValues($product);
                break;
        }

        return $isAllowed;
    }

    /**
     * Return whether the current user is allowed to update family of the product
     *
     * @param ProductInterface $product
     *
     * @return bool
     */
    protected function isAllowedToUpdateFamily(ProductInterface $product)
    {
        return $this->checkAclForType('family');
    }

    /**
     * Return whether the current user is allowed to update groups of the product
     *
     * @param ProductInterface $product
     *
     * @return bool
     */
    protected function isAllowedToUpdateGroups(ProductInterface $product)
    {
        return $this->checkAclForType('groups');
    }

    /**
     * Return whether the current user is allowed to update categories of the product
     *
     * @param ProductInterface $product
     *
     * @return bool
     */
    protected function isAllowedToClassify(ProductInterface $product)
    {
        return $this->checkAclForType('categories');
    }

    /**
     * Return whether the current user is allowed to update status of the product
     *
     * @param ProductInterface $product
     *
     * @return bool
     */
    protected function isAllowedToUpdateStatus(ProductInterface $product)
    {
        return $this->checkAclForType('enabled');
    }

    /**
     * Return whether the current user is allowed to update associations of the product
     *
     * @param ProductInterface $product
     *
     * @return bool
     */
    protected function isAllowedToUpdateAssociations(ProductInterface $product)
    {
        return $this->checkAclForType('associations');
    }

    /**
     * Return whether the current user is allowed to update product values of the product
     *
     * @param ProductInterface $product
     *
     * @return bool
     */
    protected function isAllowedToUpdateValues(ProductInterface $product)
    {
        return $this->checkAclForType('values');
    }

    /**
     * Return whether the current user has ACL to do the given modification $type on the product
     *
     * @param $type
     *
     * @return bool
     */
    protected function checkAclForType($type)
    {
        $acl = $this->getAclForType($type);

        return null === $acl || $this->securityFacade->isGranted($acl);
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
