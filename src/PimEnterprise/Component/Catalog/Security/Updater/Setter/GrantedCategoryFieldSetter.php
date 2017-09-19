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

use Akeneo\Component\Classification\CategoryAwareInterface;
use Akeneo\Component\StorageUtils\Exception\InvalidPropertyException;
use Doctrine\Common\Persistence\ObjectRepository;
use Pim\Component\Catalog\Updater\Setter\AbstractFieldSetter;
use Pim\Component\Catalog\Updater\Setter\FieldSetterInterface;
use PimEnterprise\Component\Security\Attributes;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\InvalidArgumentException;

/**
 * Check if category is at least "viewable" to be associated to a product
 *
 * @author Marie Bochu <marie.bochu@akeneo.com>
 */
class GrantedCategoryFieldSetter extends AbstractFieldSetter implements FieldSetterInterface
{
    /** @var FieldSetterInterface */
    private $categoryFieldSetter;

    /** @var AuthorizationCheckerInterface */
    private $authorizationChecker;

    /** @var ObjectRepository */
    private $categoryAccessRepository;

    /** @var TokenStorageInterface */
    private $tokenStorage;

    /**
     * @param FieldSetterInterface            $categoryFieldSetter
     * @param AuthorizationCheckerInterface   $authorizationChecker
     * @param ObjectRepository                $categoryAccessRepository
     * @param TokenStorageInterface           $tokenStorage
     * @param array                           $supportedFields
     */
    public function __construct(
        FieldSetterInterface $categoryFieldSetter,
        AuthorizationCheckerInterface $authorizationChecker,
        ObjectRepository $categoryAccessRepository,
        TokenStorageInterface $tokenStorage,
        array $supportedFields
    ) {
        $this->categoryFieldSetter = $categoryFieldSetter;
        $this->authorizationChecker = $authorizationChecker;
        $this->categoryAccessRepository = $categoryAccessRepository;
        $this->tokenStorage = $tokenStorage;
        $this->supportedFields = $supportedFields;
    }

    /**
     * {@inheritdoc}
     */
    public function setFieldData($product, $field, $data, array $options = [])
    {
        $areCategoriesVisible = $this->areAllCategoriesVisibleOnProduct($product);
        $wasOwner = $this->authorizationChecker->isGranted([Attributes::OWN], $product);

        $this->categoryFieldSetter->setFieldData($product, $field, $data, $options);

        $isOwner = false;
        foreach ($product->getCategories() as $category) {
            if (!$this->authorizationChecker->isGranted([Attributes::VIEW_ITEMS], $category)) {
                throw InvalidPropertyException::validEntityCodeExpected(
                    $field,
                    'category code',
                    'The category does not exist',
                    static::class,
                    $category->getCode()
                );
            }
            if ($this->authorizationChecker->isGranted([Attributes::OWN_PRODUCTS], $category)) {
                $isOwner = true;
            }
        }

        if (count($product->getCategories()) === 0 && $areCategoriesVisible) {
            $isOwner = true;
        }

        if ($wasOwner && !$isOwner && null !== $product->getId()) {
            throw new InvalidArgumentException(
                'You should at least keep your product in one category on which you have an own permission.'
            );
        }
    }

    /**
     * In the case the user removes all categories from a product he owns, he will not loose the ownership of product
     * (because an uncategorized product is automatically owned) except if there is still a category the user can not
     * view attached to the product.
     * This method check if there are categories he can not view on the product.
     *
     * @param CategoryAwareInterface $product
     *
     * @return bool
     */
    protected function areAllCategoriesVisibleOnProduct(CategoryAwareInterface $product)
    {
        $categoryCodes = $product->getCategoryCodes();
        if (count($categoryCodes) === 0) {
            return true;
        }
        $user = $this->tokenStorage->getToken()->getUser();

        return $this->categoryAccessRepository->areAllCategoryCodesGranted($user, Attributes::VIEW_ITEMS, $categoryCodes);
    }
}
