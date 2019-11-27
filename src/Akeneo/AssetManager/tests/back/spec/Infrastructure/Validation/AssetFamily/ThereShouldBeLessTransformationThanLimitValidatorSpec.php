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
use Akeneo\AssetManager\Infrastructure\Validation\AssetFamily\ThereShouldBeLessTransformationThanLimit;
use Akeneo\AssetManager\Infrastructure\Validation\AssetFamily\ThereShouldBeLessTransformationThanLimitValidator;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class ThereShouldBeLessTransformationThanLimitValidatorSpec extends ObjectBehavior
{
    public function let(ExecutionContextInterface $context)
    {
        $this->beConstructedWith(2);
        $this->initialize($context);
    }

    public function it_is_initializable()
    {
        $this->shouldBeAnInstanceOf(ThereShouldBeLessTransformationThanLimitValidator::class);
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
                'target' => 'attr4',
                'operations' => [
                    ['type' => 'type1'],
                ],
            ],
        ];
        $command = new EditAssetFamilyCommand('id', [], [], null, [], $transformations);
        $context->buildViolation(Argument::any())->shouldNotBeCalled();

        $this->validate($command, new ThereShouldBeLessTransformationThanLimit());
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
                'operations' => [],
            ],
            [
                'source' => 'attr1',
                'target' => 'attr4',
                'operations' => [],
            ],
        ];
        $command = new EditAssetFamilyCommand('id', [], [], null, [], $transformations);
        $context->buildViolation(ThereShouldBeLessTransformationThanLimit::ERROR_MESSAGE)
            ->willReturn($constraintViolationBuilder, $constraintViolationBuilder);
        $constraintViolationBuilder->setParameter('%asset_family_identifier%', 'id')->willReturn($constraintViolationBuilder);
        $constraintViolationBuilder->setParameter('%limit%', 2)->willReturn($constraintViolationBuilder);
        $constraintViolationBuilder->atPath('transformations')->willReturn($constraintViolationBuilder);
        $constraintViolationBuilder->addViolation()->shouldBeCalledOnce();

        $this->validate($command, new ThereShouldBeLessTransformationThanLimit());
    }
}
