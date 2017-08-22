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

use Akeneo\Component\StorageUtils\Exception\InvalidPropertyException;
use Pim\Component\Catalog\Updater\Setter\AbstractFieldSetter;
use Pim\Component\Catalog\Updater\Setter\FieldSetterInterface;
use PimEnterprise\Component\Security\Attributes;
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

    /**
     * @param FieldSetterInterface          $categoryFieldSetter
     * @param AuthorizationCheckerInterface $authorizationChecker
     * @param array                         $supportedFields
     */
    public function __construct(
        FieldSetterInterface $categoryFieldSetter,
        AuthorizationCheckerInterface $authorizationChecker,
        array $supportedFields
    ) {
        $this->categoryFieldSetter = $categoryFieldSetter;
        $this->authorizationChecker = $authorizationChecker;
        $this->supportedFields = $supportedFields;
    }

    /**
     * {@inheritdoc}
     */
    public function setFieldData($product, $field, $data, array $options = [])
    {
        $wasOwner = $this->authorizationChecker->isGranted([Attributes::OWN], $product);

        $this->categoryFieldSetter->setFieldData($product, $field, $data, $options);

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
        }

        $isOwner = $this->authorizationChecker->isGranted([Attributes::OWN], $product);

        if ($wasOwner && !$isOwner && null !== $product->getId()) {
            throw new InvalidArgumentException(
                'You should at least keep your product in one category on which you have an own permission.'
            );
        }
    }
}
