<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2017 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Permission\Component\Updater\Setter;

use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithValuesInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Enrichment\Component\Product\Updater\Setter\AbstractFieldSetter;
use Akeneo\Pim\Enrichment\Component\Product\Updater\Setter\FieldSetterInterface;
use Akeneo\Pim\Permission\Bundle\Entity\Repository\CategoryAccessRepository;
use Akeneo\Pim\Permission\Component\Attributes;
use Akeneo\Tool\Component\Classification\CategoryAwareInterface;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyException;
use Akeneo\UserManagement\Component\Model\UserInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\Util\ClassUtils;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\InvalidArgumentException;
use Webmozart\Assert\Assert;

/**
 * Check if category is at least "viewable" to be associated to a resource
 *
 * @author Marie Bochu <marie.bochu@akeneo.com>
 */
class GrantedCategoryFieldSetter extends AbstractFieldSetter implements FieldSetterInterface
{
    /** @var FieldSetterInterface */
    private $categoryFieldSetter;

    /** @var AuthorizationCheckerInterface */
    private $authorizationChecker;

    /** @var CategoryAccessRepository */
    private $categoryAccessRepository;

    /** @var TokenStorageInterface */
    private $tokenStorage;

    /** @var ObjectManager */
    private $entityManager;

    public function __construct(
        FieldSetterInterface $categoryFieldSetter,
        AuthorizationCheckerInterface $authorizationChecker,
        CategoryAccessRepository $categoryAccessRepository,
        TokenStorageInterface $tokenStorage,
        ObjectManager $entityManager,
        array $supportedFields
    ) {
        $this->categoryFieldSetter = $categoryFieldSetter;
        $this->authorizationChecker = $authorizationChecker;
        $this->categoryAccessRepository = $categoryAccessRepository;
        $this->tokenStorage = $tokenStorage;
        $this->entityManager = $entityManager;
        $this->supportedFields = $supportedFields;
    }

    /**
     * {@inheritdoc}
     */
    public function setFieldData($entityWithCategories, $field, $data, array $options = [])
    {
        $areCategoriesVisible = $this->areAllCategoriesVisibleOnEntity($entityWithCategories);
        $wasOwner = $this->authorizationChecker->isGranted([Attributes::OWN], $entityWithCategories);
        if ($entityWithCategories instanceof ProductModelInterface &&
            $this->authorizationChecker->isGranted(Attributes::EDIT, $entityWithCategories)
        ) {
            $wasOwner = true;
        }

        $this->categoryFieldSetter->setFieldData($entityWithCategories, $field, $data, $options);

        $isOwner = false;

        // TODO: refactor it by introducing a method getCategoriesForVariation for ProductModelInterface
        // TODO: and a common interface with ProductInterface
        $categories = $entityWithCategories instanceof ProductInterface ?
            $entityWithCategories->getCategoriesForVariation() : $entityWithCategories->getCategories();

        foreach ($categories as $category) {
            if (!$this->authorizationChecker->isGranted([Attributes::VIEW_ITEMS], $category)) {
                throw InvalidPropertyException::validEntityCodeExpected(
                    $field,
                    'category code',
                    'The category does not exist',
                    static::class,
                    $category->getCode()
                );
            }
        }

        foreach ($entityWithCategories->getCategories() as $category) {
            if ($entityWithCategories instanceof ProductModelInterface &&
                $this->authorizationChecker->isGranted(Attributes::EDIT_ITEMS, $category)
            ) {
                $isOwner = true;
            } elseif ($this->authorizationChecker->isGranted([Attributes::OWN_PRODUCTS], $category)) {
                $isOwner = true;
            }
        }

        if (count($entityWithCategories->getCategories()) === 0 && $areCategoriesVisible) {
            $isOwner = true;
        }

        if ($wasOwner && !$isOwner && null !== $entityWithCategories->getId()) {
            throw new InvalidArgumentException(
                'You should at least keep your product in one category on which you have an own permission.'
            );
        }
    }

    /**
     * In the case the user removes all categories from an resource he owns, he will not loose the ownership of this resource
     * (because an uncategorized resource is automatically owned) except if there is still a category the user can not
     * view attached to the resource.
     * This method check if there are categories he can not view on the resource.
     *
     * @param CategoryAwareInterface $entityWithCategories
     *
     * @return bool
     */
    protected function areAllCategoriesVisibleOnEntity(CategoryAwareInterface $entityWithCategories)
    {
        Assert::implementsInterface($entityWithCategories, EntityWithValuesInterface::class);
        if (null === $entityWithCategories->getId()) {
            return true;
        }

        $entityWithoutPermission = $this->entityManager->getRepository(ClassUtils::getClass($entityWithCategories))
            ->find($entityWithCategories->getId());
        if (null === $entityWithoutPermission) {
            return true;
        }

        $categoryCodes = $entityWithoutPermission->getCategoryCodes();
        if (count($categoryCodes) === 0) {
            return true;
        }
        $user = $this->tokenStorage->getToken()->getUser();
        Assert::implementsInterface($user, UserInterface::class);

        return $this->categoryAccessRepository->areAllCategoryCodesGranted(
            $user,
            Attributes::VIEW_ITEMS,
            $categoryCodes
        );
    }
}
