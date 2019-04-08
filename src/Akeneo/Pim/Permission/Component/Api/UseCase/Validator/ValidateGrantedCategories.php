<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Permission\Component\Api\UseCase\Validator;

use Akeneo\Pim\Enrichment\Component\Product\Connector\UseCase\Validator\ValidateGrantedCategoriesInterface;
use Akeneo\Pim\Permission\Component\Attributes;
use Akeneo\Tool\Component\Api\Exception\InvalidQueryException;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Webmozart\Assert\Assert;

/**
 * @author Pierre Allard <pierre.allard@akeneo.com>
 */
final class ValidateGrantedCategories implements ValidateGrantedCategoriesInterface
{
    /** @var IdentifiableObjectRepositoryInterface */
    private $categoryRepository;

    /** @var AuthorizationCheckerInterface */
    private $authorizationChecker;

    public function __construct(
        IdentifiableObjectRepositoryInterface $categoryRepository,
        AuthorizationCheckerInterface $authorizationChecker
    ) {
        $this->categoryRepository = $categoryRepository;
        $this->authorizationChecker = $authorizationChecker;
    }

    /**
     * {@inheritdoc}
     */
    public function validate(array $search): void
    {
        if (!isset($search['categories']) || !is_array($search['categories'])) {
            return;
        }

        $categoryFilters = $search['categories'];

        $errors = [];
        foreach ($categoryFilters as $categoryFilter) {
            foreach ($categoryFilter['value'] as $categoryCode) {
                $categoryCode = trim($categoryCode);
                $categoryFilter = $this->categoryRepository->findOneByIdentifier($categoryCode);
                Assert::notNull($categoryFilter);

                if (!$this->authorizationChecker->isGranted(Attributes::VIEW_ITEMS, $categoryFilter)) {
                    $errors[] = $categoryFilter->getCode();
                }
            }
        }

        if (!empty($errors)) {
            $plural = count($errors) > 1 ? 'Categories "%s" do not exist.' : 'Category "%s" does not exist.';
            throw new InvalidQueryException(sprintf($plural, implode(', ', $errors)));
        }
    }
}
