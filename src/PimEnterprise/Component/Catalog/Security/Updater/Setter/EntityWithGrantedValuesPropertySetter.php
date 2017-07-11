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

use Akeneo\Component\StorageUtils\Exception\ResourceNotFoundException;
use Akeneo\Component\StorageUtils\Exception\UnknownPropertyException;
use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Component\StorageUtils\Updater\PropertySetterInterface;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\LocaleInterface;
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
            throw new ResourceNotFoundException(AttributeInterface::class);
        }

        $channel = $options['scope'];
        $locale = $options['locale'];

        $oldValue = $entityWithValues->getValue($field, $locale, $channel);
        $this->propertySetter->setData($entityWithValues, $field, $data, $options);
        $newValue = $entityWithValues->getValue($field, $locale, $channel);

        $this->checkGrantedAttributeGroup($attribute, $oldValue, $newValue);
        $this->checkGrantedLocalizableAttribute($attribute, $oldValue, $newValue);
    }

    /**
     * Check if an attribute belongs to a granted attribute group
     *
     * @param AttributeInterface  $attribute
     * @param ValueInterface|null $oldValue
     * @param ValueInterface|null $newValue
     *
     * @throws UnknownPropertyException
     * @throws ResourceAccessDeniedException
     */
    private function checkGrantedAttributeGroup(
        AttributeInterface $attribute,
        ValueInterface $oldValue = null,
        ValueInterface $newValue = null
    ) {
        $canEdit = $this->authorizationChecker->isGranted([Attributes::EDIT_ATTRIBUTES], $attribute);
        $canView = $this->authorizationChecker->isGranted([Attributes::VIEW_ATTRIBUTES], $attribute);

        if (!$canView && !$canEdit) {
            throw UnknownPropertyException::unknownProperty($attribute->getCode());
        }

        if ($canView && !$canEdit) {
            $valueIsDeleted = null !== $oldValue && $oldValue->hasData() && null === $newValue;
            $valueIsAdded = null === $oldValue && null !== $newValue && $newValue->hasData();
            $valueIsChanged = null !== $oldValue && null !== $newValue && !$oldValue->isEqual($newValue);

            if ($valueIsChanged || $valueIsAdded || $valueIsDeleted) {
                throw new ResourceAccessDeniedException($newValue, sprintf(
                    'Attribute "%s" belongs to the attribute group "%s" on which you only have view permission.',
                    $attribute->getCode(),
                    $attribute->getGroup()->getCode()
                ));
            }
        }
    }

    /**
     * Check if a locale is granted
     *
     * @param AttributeInterface $attribute
     * @param ValueInterface     $oldValue
     * @param ValueInterface     $newValue
     */
    private function checkGrantedLocalizableAttribute(
        AttributeInterface $attribute,
        ValueInterface $oldValue = null,
        ValueInterface $newValue = null
    ) {
        if (!$attribute->isLocalizable() || !$attribute->isLocaleSpecific() && null === $newValue->getLocale()) {
            return;
        }

        $locale = $this->localeRepository->findOneByIdentifier($newValue->getLocale());
        $canView = $this->authorizationChecker->isGranted([Attributes::VIEW_ITEMS], $locale);
        $canEdit = $this->authorizationChecker->isGranted([Attributes::EDIT_ITEMS], $locale);

        if (!$canView && !$canEdit) {
            throw new UnknownPropertyException($locale->getCode(), sprintf(
                'Attribute "%s" expects an existing and activated locale, "%s" given.',
                $attribute->getCode(),
                $locale->getCode()
            ));
        }

        if ($canView && !$canEdit) {
            $valueIsDeleted = null !== $oldValue && $oldValue->hasData() && null === $newValue;
            $valueIsAdded = null === $oldValue && null !== $newValue && $newValue->hasData();
            $valueIsChanged = null !== $oldValue && null !== $newValue && !$oldValue->isEqual($newValue);

            if ($valueIsChanged || $valueIsAdded || $valueIsDeleted) {
                throw new ResourceAccessDeniedException($newValue, sprintf(
                    'You only have a view permission on the locale "%s".',
                    $locale->getCode()
                ));
            }
        }
    }
}
