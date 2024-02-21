<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Automation\IdentifierGenerator\Infrastructure\Validation;

use Akeneo\Category\ServiceApi\Category;
use Akeneo\Category\ServiceApi\CategoryQueryInterface;
use Akeneo\Pim\Automation\IdentifierGenerator\Infrastructure\Validation\CategoryCodesShouldExist;
use Akeneo\Pim\Automation\IdentifierGenerator\Infrastructure\Validation\CategoryCodesShouldExistValidator;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Context\ExecutionContext;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CategoryCodesShouldExistValidatorSpec extends ObjectBehavior
{
    public function let(
        CategoryQueryInterface $categoryQuery,
        ExecutionContext $executionContext,
    ): void {
        $this->beConstructedWith($categoryQuery);
        $this->initialize($executionContext);
    }

    public function it_is_initializable(): void
    {
        $this->shouldHaveType(CategoryCodesShouldExistValidator::class);
    }

    public function it_can_only_validate_the_right_constraint(): void
    {
        $this->shouldThrow(\InvalidArgumentException::class)
            ->during('validate', [['type' => 'category', 'operator' => 'IN', 'value' => ['shirts']], new NotBlank()]);
    }

    public function it_should_not_validate_if_category_codes_is_not_an_array(
        ExecutionContext $executionContext,
    ): void {
        $categoryCodes = 'foo';
        $executionContext->buildViolation((string)Argument::any())->shouldNotBeCalled();
        $this->validate($categoryCodes, new CategoryCodesShouldExist());
    }

    public function it_should_not_validate_if_category_codes_is_empty(
        ExecutionContext $executionContext,
    ): void {
        $categoryCodes = [];

        $executionContext->buildViolation((string)Argument::any())->shouldNotBeCalled();
        $this->validate($categoryCodes, new CategoryCodesShouldExist());
    }

    public function it_should_not_validate_if_category_codes_is_not_an_array_of_strings(
        ExecutionContext $executionContext,
    ): void {
        $categoryCodes = ['shirts', true];

        $executionContext->buildViolation((string)Argument::any())->shouldNotBeCalled();
        $this->validate($categoryCodes, new CategoryCodesShouldExist());
    }

    public function it_should_not_build_violation_if_categories_exist(
        ExecutionContext $executionContext,
        CategoryQueryInterface $categoryQuery,
    ): void {
        $categoryCodes = ['shirts'];

        $shirtCategory = new Category(1, 'shirts');
        $categoryQuery
            ->byCodes($categoryCodes)
            ->shouldBeCalled()
            ->willReturn($this->arrayAsGenerator([$shirtCategory]));

        $executionContext->buildViolation((string)Argument::any())->shouldNotBeCalled();
        $this->validate($categoryCodes, new CategoryCodesShouldExist());
    }

    public function it_should_build_violation_if_categories_do_not_exist(
        ExecutionContext $executionContext,
        ConstraintViolationBuilderInterface $constraintViolationBuilder,
        CategoryQueryInterface $categoryQuery,
    ): void {
        $categoryCodes = ['shirts', 'unknown_category1', 'unknown_category2'];

        $shirtCategory = new Category(1, 'shirts');
        $categoryQuery
            ->byCodes($categoryCodes)
            ->shouldBeCalled()
            ->willReturn($this->arrayAsGenerator([$shirtCategory]));

        $executionContext
            ->buildViolation(
                'validation.identifier_generator.categories_do_not_exist',
                ['{{ categoryCodes }}' => '"unknown_category1", "unknown_category2"']
            )
            ->shouldBeCalled()
            ->willReturn($constraintViolationBuilder);

        $constraintViolationBuilder->addViolation()->shouldBeCalled();
        $this->validate($categoryCodes, new CategoryCodesShouldExist());
    }

    private function arrayAsGenerator(array $array): \Generator
    {
        foreach ($array as $item) {
            yield $item;
        }
    }
}
