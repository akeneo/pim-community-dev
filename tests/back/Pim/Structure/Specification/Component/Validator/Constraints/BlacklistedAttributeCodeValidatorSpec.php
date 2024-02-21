<?php

namespace Specification\Akeneo\Pim\Structure\Component\Validator\Constraints;

use Akeneo\Pim\Structure\Component\Query\InternalApi\GetBlacklistedAttributeJobExecutionIdInterface;
use Akeneo\Pim\Structure\Component\Query\InternalApi\IsAttributeCodeBlacklistedInterface;
use Akeneo\Pim\Structure\Component\Validator\Constraints\BlacklistedAttributeCode;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Translation\Translator;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class BlacklistedAttributeCodeValidatorSpec extends ObjectBehavior
{
    function let(
        ExecutionContextInterface $context,
        IsAttributeCodeBlacklistedInterface $isAttributeCodeBlacklisted,
        GetBlacklistedAttributeJobExecutionIdInterface $getBlacklistedAttributeJobExecutionId,
        Translator $translator,
        RouterInterface $router
    ) {
        $this->beConstructedWith(
            $isAttributeCodeBlacklisted,
            $getBlacklistedAttributeJobExecutionId,
            $translator,
            $router
        );

        $this->initialize($context);
    }

    function it_does_not_add_violations_if_attribute_is_not_blacklisted(
        ExecutionContextInterface $context,
        IsAttributeCodeBlacklistedInterface $isAttributeCodeBlacklisted,
        BlacklistedAttributeCode $constraint
    ) {
        $isAttributeCodeBlacklisted->execute('my_attribute_code')->willReturn(false);
        $context->buildViolation(Argument::cetera())->shouldNotBeCalled();

        $this->validate('my_attribute_code', $constraint);
    }

    function it_add_violation_if_attribute_is_blacklisted(
        ExecutionContextInterface $context,
        IsAttributeCodeBlacklistedInterface $isAttributeCodeBlacklisted,
        BlacklistedAttributeCode $constraint,
        ConstraintViolationBuilderInterface $violation
    ) {
        $isAttributeCodeBlacklisted->execute('my_attribute_code')->willReturn(true);
        $context
            ->buildViolation('pim_catalog.constraint.blacklisted_attribute_code')
            ->shouldBeCalled()
            ->willReturn($violation);

        $this->validate('my_attribute_code', $constraint);
    }
}
