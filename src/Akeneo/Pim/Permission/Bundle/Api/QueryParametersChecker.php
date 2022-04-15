<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2017 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Permission\Bundle\Api;

use Akeneo\Channel\Infrastructure\Component\Model\ChannelInterface;
use Akeneo\Pim\Permission\Component\Attributes;
use Akeneo\Tool\Bundle\ApiBundle\Checker\QueryParametersCheckerInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
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
        $localeCodes = array_map('trim', $localeCodes);

        $this->queryParametersChecker->checkLocalesParameters($localeCodes, $channel);

        $errors = [];
        foreach ($localeCodes as $localeCode) {
            $locale = $this->localeRepository->findOneByIdentifier($localeCode);

            if (!$this->authorizationChecker->isGranted(Attributes::VIEW_ITEMS, $locale)) {
                $errors[] = $localeCode;
            }
        }

        if (!empty($errors)) {
            $plural = count($errors) > 1 ?
                'Locales "%s" do not exist or are not activated.' : 'Locale "%s" does not exist or is not activated.';
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
            $attributeCode = trim($attributeCode);
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
            foreach ($category['value'] as $categoryCode) {
                $categoryCode = trim($categoryCode);
                $category = $this->categoryRepository->findOneByIdentifier($categoryCode);

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

    /**
     * {@inheritdoc}
     */
    public function checkPropertyParameters(string $property, string $operator)
    {
        $this->queryParametersChecker->checkPropertyParameters($property, $operator);

        $property = trim($property);
        $attribute = $this->attributeRepository->findOneByIdentifier($property);

        if (null !== $attribute) {
            $group = $attribute->getGroup();

            if (!$this->authorizationChecker->isGranted(Attributes::VIEW_ATTRIBUTES, $group)) {
                throw new UnprocessableEntityHttpException(
                    sprintf(
                        'Filter on property "%s" is not supported or does not support operator "%s"',
                        $property,
                        $operator
                    )
                );
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function checkCriterionParameters(string $searchString): array
    {
        return $this->queryParametersChecker->checkCriterionParameters($searchString);
    }
}
