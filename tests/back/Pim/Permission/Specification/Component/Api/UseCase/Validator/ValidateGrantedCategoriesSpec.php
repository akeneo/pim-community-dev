<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Permission\Component\Api\UseCase\Validator;

use Akeneo\Pim\Enrichment\Component\Category\Model\Category;
use Akeneo\Tool\Component\Api\Exception\InvalidQueryException;
use Akeneo\Pim\Permission\Component\Attributes;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class ValidateGrantedCategoriesSpec extends ObjectBehavior
{
    function let(
        IdentifiableObjectRepositoryInterface $categoryRepository,
        AuthorizationCheckerInterface $authorizationChecker
    )
    {
        $this->beConstructedWith($categoryRepository, $authorizationChecker);
    }

    function it_does_not_throw_exception_when_there_is_no_category_filter() {
        $this
            ->shouldNotThrow(InvalidQueryException::class)
            ->during('validate', [[]]);
    }

    function it_does_not_throw_exception_when_categories_are_granted(
        IdentifiableObjectRepositoryInterface $categoryRepository,
        AuthorizationCheckerInterface $authorizationChecker
    ) {

        $shoes = new Category();
        $categoryRepository->findOneByIdentifier('shoes')->willReturn($shoes);
        $trousers = new Category();
        $categoryRepository->findOneByIdentifier('trousers')->willReturn($trousers);

        $authorizationChecker->isGranted(Attributes::VIEW_ITEMS, $shoes)->willReturn(true);
        $authorizationChecker->isGranted(Attributes::VIEW_ITEMS, $trousers)->willReturn(true);

        $this
            ->shouldNotThrow(InvalidQueryException::class)
            ->during('validate', [
                [
                    'categories' => [
                        ['value' => ['shoes']],
                        ['value' => ['trousers']]
                    ]
                ]
            ]);
    }

    function it_throws_an_exception_when_categories_are_granted(
        IdentifiableObjectRepositoryInterface $categoryRepository,
        AuthorizationCheckerInterface $authorizationChecker
    ) {

        $shoes = new Category();
        $categoryRepository->findOneByIdentifier('shoes')->willReturn($shoes);
        $trousers = new Category();
        $categoryRepository->findOneByIdentifier('trousers')->willReturn($trousers);

        $authorizationChecker->isGranted(Attributes::VIEW_ITEMS, $shoes)->willReturn(true);
        $authorizationChecker->isGranted(Attributes::VIEW_ITEMS, $trousers)->willReturn(false);

        $this
            ->shouldThrow(InvalidQueryException::class)
            ->during('validate', [
                [
                    'categories' => [
                        ['value' => ['shoes']],
                        ['value' => ['trousers']]
                    ]
                ]
            ]);
    }
}
