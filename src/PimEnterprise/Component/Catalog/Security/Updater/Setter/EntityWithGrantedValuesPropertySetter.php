<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2017 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Component\Catalog\Security\Updater\Setter;

use Akeneo\Component\StorageUtils\Exception\UnknownPropertyException;
use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Component\StorageUtils\Updater\PropertySetterInterface;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\ValueInterface;
use PimEnterprise\Component\Security\Attributes;
use PimEnterprise\Component\Security\Exception\ResourceAccessDeniedException;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * Check if the property is granted. A property is granted if:
 *   - it belongs to a granted attribute group
 *   - it's localizable and the locale is granted
 *
 * If property is only "viewable", we accept the submission from the moment it has not been modified.
 *
 * @author Marie Bochu <marie.bochu@akeneo.com>
 */
class EntityWithGrantedValuesPropertySetter implements PropertySetterInterface
{
    /** @var PropertySetterInterface */
    private $propertySetter;

    /** @var AuthorizationCheckerInterface */
    private $authorizationChecker;

    /** @var IdentifiableObjectRepositoryInterface */
    private $attributeRepository;

    /** @var IdentifiableObjectRepositoryInterface */
    private $localeRepository;

    /**
     * @param PropertySetterInterface               $propertySetter
     * @param AuthorizationCheckerInterface         $authorizationChecker
     * @param IdentifiableObjectRepositoryInterface $attributeRepository
     * @param IdentifiableObjectRepositoryInterface $localeRepository
     */
    public function __construct(
        PropertySetterInterface $propertySetter,
        AuthorizationCheckerInterface $authorizationChecker,
        IdentifiableObjectRepositoryInterface $attributeRepository,
        IdentifiableObjectRepositoryInterface $localeRepository
    ) {
        $this->propertySetter = $propertySetter;
        $this->authorizationChecker = $authorizationChecker;
        $this->attributeRepository = $attributeRepository;
        $this->localeRepository = $localeRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function setData($entityWithValues, $field, $data, array $options = [])
    {
        $attribute = $this->attributeRepository->findOneByIdentifier($field);
        if (null === $attribute) {
            $this->propertySetter->setData($entityWithValues, $field, $data, $options);

            return;
        }

        $channelCode = $options['scope'];
        $localeCode = $options['locale'];

        $permissions = [
            'view_attribute' => $this->authorizationChecker->isGranted([Attributes::VIEW_ATTRIBUTES], $attribute),
            'edit_attribute' => $this->authorizationChecker->isGranted([Attributes::EDIT_ATTRIBUTES], $attribute),
            'view_locale'    => null,
            'edit_locale'    => null,
        ];

        $this->checkViewableAttributeGroup($attribute, $permissions);

        if (null !== $localeCode) {
            $locale = $this->localeRepository->findOneByIdentifier($localeCode);
            if (null === $locale) {
                throw new UnknownPropertyException($localeCode, sprintf(
                    'Attribute "%s" expects an existing and activated locale, "%s" given.',
                    $attribute->getCode(),
                    $localeCode
                ));
            }

            $permissions = array_merge($permissions, [
                'view_locale' => $this->authorizationChecker->isGranted([Attributes::VIEW_ITEMS], $locale),
                'edit_locale' => $this->authorizationChecker->isGranted([Attributes::EDIT_ITEMS], $locale)
            ]);

            $this->checkViewableLocalizableAttribute($attribute, $permissions, $localeCode);
        }

        $oldValue = $entityWithValues->getValue($field, $localeCode, $channelCode);
        $this->propertySetter->setData($entityWithValues, $field, $data, $options);
        $newValue = $entityWithValues->getValue($field, $localeCode, $channelCode);

        $this->checkEditableAttribute($attribute, $permissions, $oldValue, $newValue);
    }

    /**
     * @param AttributeInterface $attribute
     * @param array              $permissions
     */
    private function checkViewableAttributeGroup(AttributeInterface $attribute, array $permissions): void
    {
        if (!$permissions['view_attribute'] && !$permissions['edit_attribute']) {
            throw UnknownPropertyException::unknownProperty($attribute->getCode());
        }
    }

    /**
     * @param AttributeInterface $attribute
     * @param array              $permissions
     * @param string             $localeCode
     */
    private function checkViewableLocalizableAttribute(
        AttributeInterface $attribute,
        array $permissions,
        string $localeCode
    ): void {
        if (!$permissions['view_locale'] && !$permissions['edit_locale']) {
            throw new UnknownPropertyException($localeCode, sprintf(
                'Attribute "%s" expects an existing and activated locale, "%s" given.',
                $attribute->getCode(),
                $localeCode
            ));
        }
    }

    /**
     * @param AttributeInterface  $attribute
     * @param array               $permissions
     * @param ValueInterface|null $oldValue
     * @param ValueInterface|null $newValue
     */
    private function checkEditableAttribute(
        AttributeInterface $attribute,
        array $permissions,
        ValueInterface $oldValue = null,
        ValueInterface $newValue = null
    ): void {
        $valueIsDeleted = null !== $oldValue && $oldValue->hasData() && null === $newValue;
        $valueIsAdded = null === $oldValue && null !== $newValue && $newValue->hasData();
        $valueIsChanged = null !== $oldValue && null !== $newValue && !$oldValue->isEqual($newValue);

        if (!$valueIsChanged && !$valueIsAdded && !$valueIsDeleted) {
            return;
        }

        if ($permissions['view_attribute'] && !$permissions['edit_attribute']) {
            throw new ResourceAccessDeniedException($newValue, sprintf(
                'Attribute "%s" belongs to the attribute group "%s" on which you only have view permission.',
                $attribute->getCode(),
                $attribute->getGroup()->getCode()
            ));
        }

        if (null !== $newValue->getLocale() &&
            true === $permissions['view_locale'] &&
            false === $permissions['edit_locale']
        ) {
            throw new ResourceAccessDeniedException($newValue, sprintf(
                'You only have a view permission on the locale "%s".',
                $newValue->getLocale()
            ));
        }
    }
}
