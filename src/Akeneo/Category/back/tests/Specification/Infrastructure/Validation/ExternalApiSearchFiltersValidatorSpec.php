<?php

declare(strict_types=1);

namespace Specification\Akeneo\Category\Infrastructure\Validation;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ExternalApiSearchFiltersValidatorSpec extends ObjectBehavior
{
    function let(
        ValidatorInterface $validator
    ) {
        $this->beConstructedWith(
            $validator,
        );
    }

    function it_validate_empty_array()
    {
        $searchFilters = [];
        $this->validate($searchFilters);
    }

    function it_validates_code_filters(ValidatorInterface $validator)
    {
        $searchFilters = [
            "code" => [
                [
                    "operator" => "IN",
                    "value" => ["master"]
                ]
            ],
            "is_root" => [
                [
                    "operator" => "=",
                    "value" => true
                ]
            ],
        ];
        $validator->validate(Argument::any(), Argument::any())->shouldBeCalled()->willReturn(new ConstraintViolationList());
        $this->validate($searchFilters);
    }

    function it_validates_parent_filters(ValidatorInterface $validator)
    {
        $searchFilters = [
            "parent" => [
                [
                    "operator" => "=",
                    "value" => "master",
                ]
            ],
            "is_root" => [
                [
                    "operator" => "=",
                    "value" => true,
                ]
            ],
        ];
        $validator->validate(Argument::any(), Argument::any())->shouldBeCalled()->willReturn(new ConstraintViolationList());
        $this->validate($searchFilters);
    }

    function it_validates_updated_filters(ValidatorInterface $validator)
    {
        $searchFilters = [
            "is_root" => [
                [
                    "operator" => "=",
                    "value" => true,
                ]
            ],
            "updated" => [
                [
                    "operator" => ">",
                    "value" => '2019-06-09T12:00:00+00:00',
                ]
            ]
        ];
        $validator->validate(Argument::any(), Argument::any())->shouldBeCalled()->willReturn(new ConstraintViolationList());
        $this->validate($searchFilters);
    }

    function it_throws_exception_on_wrong_filter(ValidatorInterface $validator)
    {
        $searchFilters = [
            "test" => [
                [
                    "operator" => "IN",
                    "value" => ["master"],
                ]
            ],
        ];
        $this->shouldThrow(\InvalidArgumentException::class)->duringValidate($searchFilters);
    }

    function it_throws_exception_on_validation_filter(
        ValidatorInterface $validator,
        ConstraintViolationInterface $violation,
    )
    {
        $searchFilters = [
            "code" => [
                [
                    "operator" => "IN",
                    "value" => ["master"],
                ]
            ],
        ];

        $violations = [
            $violation->getWrappedObject()
        ];
        $validator->validate(Argument::any(), Argument::any())->shouldBeCalled()->willReturn(new ConstraintViolationList($violations));
        $this->shouldThrow(\InvalidArgumentException::class)->duringValidate($searchFilters);
    }
}
