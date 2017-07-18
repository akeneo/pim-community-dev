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
use Pim\Bundle\ApiBundle\Checker\QueryParametersCheckerInterface;
use Pim\Component\Catalog\Model\ChannelInterface;
use PimEnterprise\Component\Security\Attributes;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * @author Olivier Soulet <olivier.soulet@akeneo.com>
 */
class QueryParametersChecker implements QueryParametersCheckerInterface
{
    /** @var QueryParametersCheckerInterface */
    private $queryParametersChecker;

    /** @var AuthorizationCheckerInterface */
    private $authorizationChecker;

    /** @var IdentifiableObjectRepositoryInterface */
    private $localeRepository;

    /** @var IdentifiableObjectRepositoryInterface */
    private $attributeRepository;

    /** @var IdentifiableObjectRepositoryInterface */
    private $categoryRepository;

    /**
     * @param QueryParametersCheckerInterface       $queryParametersChecker
     * @param AuthorizationCheckerInterface         $authorizationChecker
     * @param IdentifiableObjectRepositoryInterface $localeRepository
     * @param IdentifiableObjectRepositoryInterface $attributeRepository
     * @param IdentifiableObjectRepositoryInterface $categoryRepository
     */
    public function __construct(
        QueryParametersCheckerInterface $queryParametersChecker,
        AuthorizationCheckerInterface $authorizationChecker,
        IdentifiableObjectRepositoryInterface $localeRepository,
        IdentifiableObjectRepositoryInterface $attributeRepository,
        IdentifiableObjectRepositoryInterface $categoryRepository
    ) {
        $this->queryParametersChecker = $queryParametersChecker;
        $this->authorizationChecker = $authorizationChecker;
        $this->localeRepository = $localeRepository;
        $this->attributeRepository = $attributeRepository;
        $this->categoryRepository = $categoryRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function checkLocalesParameters(array $localeCodes, ChannelInterface $channel = null)
    {
        $this->queryParametersChecker->checkLocalesParameters($localeCodes, $channel);

        $errors = [];
        foreach ($localeCodes as $localeCode) {
            $locale = $this->localeRepository->findOneByIdentifier($localeCode);

            if (!$this->authorizationChecker->isGranted(Attributes::VIEW_ITEMS, $locale)) {
                $errors[] = $localeCode;
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
    public function checkAttributesParameters(array $attributeCodes)
    {
        $this->queryParametersChecker->checkAttributesParameters($attributeCodes);

        $errors = [];
        foreach ($attributeCodes as $attributeCode) {
            $attribute = $this->attributeRepository->findOneByIdentifier($attributeCode);

            $group = $attribute->getGroup();

            if (!$this->authorizationChecker->isGranted(Attributes::VIEW_ATTRIBUTES, $group)) {
                $errors[] = $attributeCode;
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
    public function checkCategoriesParameters(array $categories)
    {
        $this->queryParametersChecker->checkCategoriesParameters($categories);
        $errors = [];
        foreach ($categories as $category) {
            foreach ($category['value'] as $value) {
                $category = $this->categoryRepository->findOneByIdentifier($value);

                if (!$this->authorizationChecker->isGranted(Attributes::VIEW_ITEMS, $category)) {
                    $errors[] = $category->getCode();
                }
            }
        }

        if (!empty($errors)) {
            $plural = count($errors) > 1 ? 'Categories "%s" do not exist.' : 'Category "%s" does not exist.';
            throw new UnprocessableEntityHttpException(sprintf($plural, implode(', ', $errors)));
        }
    }
}
