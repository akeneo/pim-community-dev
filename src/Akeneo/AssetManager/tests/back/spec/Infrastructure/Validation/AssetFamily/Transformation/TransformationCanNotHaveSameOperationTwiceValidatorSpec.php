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

namespace spec\Akeneo\AssetManager\Infrastructure\Validation\AssetFamily\Transformation;

use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Infrastructure\Validation\AssetFamily\Transformation\TransformationCanNotHaveSameOperationTwice;
use Akeneo\AssetManager\Infrastructure\Validation\AssetFamily\Transformation\TransformationCanNotHaveSameOperationTwiceValidator;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class TransformationCanNotHaveSameOperationTwiceValidatorSpec extends ObjectBehavior
{
    public function let(ExecutionContextInterface $context)
    {
        $this->initialize($context);
    }

    public function it_is_initializable()
    {
        $this->shouldBeAnInstanceOf(TransformationCanNotHaveSameOperationTwiceValidator::class);
    }

    public function it_passes_the_validation(ExecutionContextInterface $context, ValidatorInterface $validator)
    {
        $operations = [
            ['type' => 'type1'],
            ['type' => 'type2'],
            ['type' => 'type3'],
        ];
        $context->getValidator()->willReturn($validator);
        $validator->inContext($context)->willReturn($validator);
        $validator->validate($operations, new Assert\Type('array'))->shouldBeCalled();
        $context->buildViolation(Argument::any())->shouldNotBeCalled();

        $this->validate($operations, new TransformationCanNotHaveSameOperationTwice(AssetFamilyIdentifier::fromString('id')));
    }

    public function it_builds_exception_with_same_operation_twice(
        ExecutionContextInterface $context,
        ConstraintViolationBuilderInterface $constraintViolationBuilder,
        ValidatorInterface $validator
    ) {
        $operations = [
            ['type' => 'type1'],
            ['type' => 'type2'],
            ['type' => 'type1'],
        ];
        $context->getValidator()->willReturn($validator);
        $validator->inContext($context)->willReturn($validator);
        $validator->validate($operations, new Assert\Type('array'))->shouldBeCalled();
        $context->buildViolation(TransformationCanNotHaveSameOperationTwice::ERROR_MESSAGE)
            ->willReturn($constraintViolationBuilder, $constraintViolationBuilder);
        $constraintViolationBuilder->setParameter('%asset_family_identifier%', 'id')->willReturn($constraintViolationBuilder);
        $constraintViolationBuilder->setParameter('%operation_type%', 'type1')->willReturn($constraintViolationBuilder);
        $constraintViolationBuilder->atPath('transformations')->willReturn($constraintViolationBuilder);
        $constraintViolationBuilder->addViolation()->shouldBeCalledOnce();

        $this->validate($operations, new TransformationCanNotHaveSameOperationTwice(AssetFamilyIdentifier::fromString('id')));
    }
}
