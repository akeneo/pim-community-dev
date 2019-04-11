<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Connector\UseCase\Validator;

use Akeneo\Pim\Enrichment\Component\Category\Model\Category;
use Akeneo\Tool\Component\Api\Exception\InvalidQueryException;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class ValidateCategoriesSpec extends ObjectBehavior
{
    function let(
        IdentifiableObjectRepositoryInterface $categoryRepository
    ) {
        $this->beConstructedWith($categoryRepository);
    }

    function it_does_nothing_if_there_is_no_categories_filter(
        IdentifiableObjectRepositoryInterface $categoryRepository
    ) {
        $categoryRepository->findOneByIdentifier(Argument::any())->shouldNotBeCalled();
        $this->validate([]);
    }

    function it_should_throw_exception_if_categories_is_not_an_array()
    {
        $this->shouldThrow(InvalidQueryException::class)->during('validate', [['categories' => 69]]);
    }

    function it_should_throw_exception_if_a_category_does_not_exist(
        IdentifiableObjectRepositoryInterface $categoryRepository,
        Category $category
    ) {
        $categoryRepository->findOneByIdentifier('foo')->willReturn($category);
        $categoryRepository->findOneByIdentifier('bar')->willReturn(null);
        $this->shouldThrow(InvalidQueryException::class)->during('validate', [
            ['categories' => [['value' => ['foo']], ['value' => ['bar']]]]
        ]);
    }
}
