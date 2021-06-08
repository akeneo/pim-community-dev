<?php

declare(strict_types=1);

namespace spec\Akeneo\Connectivity\Connection\Application\Webhook\Validation;

use Akeneo\Connectivity\Connection\Application\Webhook\Service\DnsLookupInterface;
use Akeneo\Connectivity\Connection\Application\Webhook\Validation\ExternalUrl;
use Akeneo\Connectivity\Connection\Application\Webhook\Validation\ExternalUrlValidator;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidatorInterface;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class ExternalUrlValidatorSpec extends ObjectBehavior
{
    public function let(
        ExecutionContextInterface $context,
        DnsLookupInterface $dnsLookup
    ): void {
        $this->beConstructedWith($dnsLookup);
        $this->initialize($context);
    }

    public function it_is_initializable(): void
    {
        $this->shouldBeAnInstanceOf(ExternalUrlValidator::class);
        $this->shouldImplement(ConstraintValidatorInterface::class);
    }

    public function it_does_not_support_other_constraints(Constraint $constraint): void
    {
        $this
            ->shouldThrow(new UnexpectedTypeException($constraint->getWrappedObject(), ExternalUrl::class))
            ->during('validate', ['', $constraint]);
    }

    public function it_does_not_add_violations_if_the_value_is_empty(
        ExecutionContextInterface $context,
        ExternalUrl $constraint
    ): void {
        $context->buildViolation(Argument::any())->shouldNotBeCalled();

        $this->validate('', $constraint);
    }

    public function it_does_not_add_violations_if_the_value_is_not_an_url(
        ExecutionContextInterface $context,
        ExternalUrl $constraint
    ): void {
        $context->buildViolation(Argument::any())->shouldNotBeCalled();

        $this->validate('not_an_url', $constraint);
    }

    public function it_adds_a_violation_if_the_dns_lookup_fails(
        ExecutionContextInterface $context,
        ConstraintViolationBuilderInterface $constraintViolationBuilder,
        DnsLookupInterface $dnsLookup,
        ExternalUrl $constraint
    ): void {
        $value = 'http://akeneo.com/foo';

        $dnsLookup->ip('akeneo.com')->willReturn(null);
        $context->buildViolation($constraint->message)->willReturn($constraintViolationBuilder);
        $constraintViolationBuilder->setParameter('{{ address }}', '"'.$value.'"')
            ->willReturn($constraintViolationBuilder);
        $constraintViolationBuilder->addViolation()->shouldBeCalled();

        $this->validate($value, $constraint);
    }

    public function it_adds_a_violation_if_the_ip_belong_to_private_range(
        ExecutionContextInterface $context,
        ConstraintViolationBuilderInterface $constraintViolationBuilder,
        DnsLookupInterface $dnsLookup,
        ExternalUrl $constraint
    ): void {
        $value = 'http://akeneo.com/foo';

        $dnsLookup->ip('akeneo.com')->willReturn('172.16.0.1');
        $context->buildViolation($constraint->message)->willReturn($constraintViolationBuilder);
        $constraintViolationBuilder->setParameter('{{ address }}', '"'.$value.'"')
            ->willReturn($constraintViolationBuilder);
        $constraintViolationBuilder->addViolation()->shouldBeCalled();

        $this->validate($value, $constraint);
    }

    public function it_does_not_add_violations_if_the_ip_is_external(
        ExecutionContextInterface $context,
        ConstraintViolationBuilderInterface $constraintViolationBuilder,
        DnsLookupInterface $dnsLookup,
        ExternalUrl $constraint
    ): void {
        $value = 'http://akeneo.com/foo';

        $dnsLookup->ip('akeneo.com')->willReturn('205.205.205.1');
        $context->buildViolation(Argument::any())->shouldNotBeCalled();

        $this->validate($value, $constraint);
    }
}
