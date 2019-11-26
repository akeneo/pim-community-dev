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

namespace spec\Akeneo\AssetManager\Infrastructure\Validation\AssetFamily;

use Akeneo\AssetManager\Application\AssetFamily\EditAssetFamily\EditAssetFamilyCommand;
use Akeneo\AssetManager\Infrastructure\Validation\AssetFamily\TransformationCanNotHaveSameOperationTwice;
use Akeneo\AssetManager\Infrastructure\Validation\AssetFamily\TransformationCanNotHaveSameOperationTwiceValidator;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
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

    public function it_passes_the_validation(ExecutionContextInterface $context)
    {
        $transformations = [
            [
                'source' => 'attr1',
                'target' => 'attr2',
                'operations' => [],
            ],
            [
                'source' => 'attr1',
                'target' => 'attr3',
                'operations' => [
                    ['type' => 'type1'],
                    ['type' => 'type2'],
                    ['type' => 'type3'],
                ],
            ],
            [
                'source' => 'attr1',
                'target' => 'attr4',
                'operations' => [
                    ['type' => 'type1'],
                ],
            ],
        ];
        $command = new EditAssetFamilyCommand('id', [], [], null, [], $transformations);
        $context->buildViolation(Argument::any())->shouldNotBeCalled();

        $this->validate($command, new TransformationCanNotHaveSameOperationTwice());
    }

    public function it_builds_exception_with_same_operation_twice(
        ExecutionContextInterface $context,
        ConstraintViolationBuilderInterface $constraintViolationBuilder
    ) {
        $transformations = [
            [
                'source' => 'attr1',
                'target' => 'attr2',
                'operations' => [],
            ],
            [
                'source' => 'attr1',
                'target' => 'attr3',
                'operations' => [
                    ['type' => 'type1'],
                    ['type' => 'type2'],
                    ['type' => 'type1'],
                ],
            ],
            [
                'source' => 'attr1',
                'target' => 'attr4',
                'operations' => [
                    ['type' => 'type4'],
                    ['type' => 'type4'],
                ],
            ],
        ];
        $command = new EditAssetFamilyCommand('id', [], [], null, [], $transformations);
        $context->buildViolation(TransformationCanNotHaveSameOperationTwice::ERROR_MESSAGE)
            ->willReturn($constraintViolationBuilder, $constraintViolationBuilder);
        $constraintViolationBuilder->setParameter('%asset_family_identifier%', 'id')->willReturn($constraintViolationBuilder);
        $constraintViolationBuilder->setParameter('%operation_type%', 'type1')->willReturn($constraintViolationBuilder);
        $constraintViolationBuilder->setParameter('%operation_type%', 'type4')->willReturn($constraintViolationBuilder);
        $constraintViolationBuilder->atPath('transformations')->willReturn($constraintViolationBuilder);
        $constraintViolationBuilder->addViolation()->shouldBeCalledTimes(2);

        $this->validate($command, new TransformationCanNotHaveSameOperationTwice());
    }
}
