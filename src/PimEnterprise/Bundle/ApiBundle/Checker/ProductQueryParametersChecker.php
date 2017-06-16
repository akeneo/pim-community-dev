<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2017 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\ApiBundle\Checker;

use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Pim\Bundle\ApiBundle\Checker\ProductQueryParametersCheckerInterface;
use Pim\Component\Catalog\Model\ChannelInterface;
use PimEnterprise\Component\Security\Attributes;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * @author Olivier Soulet <olivier.soulet@akeneo.com>
 */
class ProductQueryParametersChecker implements ProductQueryParametersCheckerInterface
{
    /** @var ProductQueryParametersCheckerInterface */
    private $productQueryParametersChecker;

    /** @var AuthorizationCheckerInterface */
    private $authorizationChecker;

    /** @var IdentifiableObjectRepositoryInterface */
    private $localeRepository;

    /** @var IdentifiableObjectRepositoryInterface */
    private $attributeRepository;

    /** @var IdentifiableObjectRepositoryInterface */
    private $categoryRepository;

    /**
     * @param ProductQueryParametersCheckerInterface $productQueryParametersChecker
     * @param AuthorizationCheckerInterface          $authorizationChecker
     * @param IdentifiableObjectRepositoryInterface  $localeRepository
     * @param IdentifiableObjectRepositoryInterface  $attributeRepository
     * @param IdentifiableObjectRepositoryInterface  $categoryRepository
     */
    public function __construct(
        ProductQueryParametersCheckerInterface $productQueryParametersChecker,
        AuthorizationCheckerInterface $authorizationChecker,
        IdentifiableObjectRepositoryInterface $localeRepository,
        IdentifiableObjectRepositoryInterface $attributeRepository,
        IdentifiableObjectRepositoryInterface $categoryRepository
    ) {
        $this->productQueryParametersChecker = $productQueryParametersChecker;
        $this->authorizationChecker = $authorizationChecker;
        $this->localeRepository = $localeRepository;
        $this->attributeRepository = $attributeRepository;
        $this->categoryRepository = $categoryRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function checkLocalesParameters(array $locales, ChannelInterface $channel = null)
    {
        $this->productQueryParametersChecker->checkLocalesParameters($locales, $channel);

        $errors = [];
        foreach ($locales as $locale) {
            $locale = $this->localeRepository->findOneByIdentifier($locale);

            if (!$this->authorizationChecker->isGranted(Attributes::VIEW_ITEMS, $locale)) {
                $errors[] = $locale;
            }
        }

        if (!empty($errors)) {
            $plural = count($errors) > 1 ? 'Locales "%s" do not exist.' : 'Locale "%s" does not exist.';
            throw new UnprocessableEntityHttpException(sprintf($plural, implode(', ', $errors)));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function checkAttributesParameters(array $attributes)
    {
        $this->productQueryParametersChecker->checkAttributesParameters($attributes);

        $errors = [];
        foreach ($attributes as $attributeCode) {
            $attribute = $this->attributeRepository->findOneByIdentifier($attributeCode);

            $group = $attribute->getGroup();

            if (!$this->authorizationChecker->isGranted(Attributes::VIEW_ATTRIBUTES, $group)) {
                $errors[] = $attribute;
            }
        }

        if (!empty($errors)) {
            $plural = count($errors) > 1 ? 'Attributes "%s" do not exist.' : 'Attribute "%s" does not exist.';
            throw new UnprocessableEntityHttpException(sprintf($plural, implode(', ', $errors)));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function checkCategoriesParameters($categories)
    {
        $this->productQueryParametersChecker->checkCategoriesParameters($categories);

        $errors = [];
        foreach ($categories as $category) {
            $category = $this->categoryRepository->findOneByIdentifier($category['value']);

            $errors = [];
            if (!$this->authorizationChecker->isGranted(Attributes::VIEW_ITEMS, $category->getCode())) {
                $errors[] = $category;
            }
        }

        if (!empty($errors)) {
            $plural = count($errors) > 1 ? 'Categories "%s" do not exist.' : 'Category "%s" does not exist.';
            throw new UnprocessableEntityHttpException(sprintf($plural, implode(', ', $errors)));
        }
    }
}
