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
        DnsLookupInterface $dnsLookup
    ): void {
        $this->beConstructedWith($dnsLookup);
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

    public function it_ignores_the_value_if_empty(
        ExecutionContextInterface $context,
        ExternalUrl $constraint
    ): void {
        $this->initialize($context);
        $value = '';

        $context->buildViolation(Argument::any())->shouldNotBeCalled();

        $this->validate($value, $constraint);
    }

    public function it_ignores_the_value_if_not_an_url(
        ExecutionContextInterface $context,
        ExternalUrl $constraint
    ): void {
        $this->initialize($context);
        $value = 'not_an_url';

        $context->buildViolation(Argument::any())->shouldNotBeCalled();

        $this->validate($value, $constraint);
    }

    public function it_ignores_the_value_if_url_cannot_be_resolved(
        ExecutionContextInterface $context,
        DnsLookupInterface $dnsLookup,
        ExternalUrl $constraint
    ): void {
        $this->initialize($context);
        $value = 'http://akeneo.com/foo';

        $dnsLookup->ip('akeneo.com')->willReturn(null);
        $context->buildViolation(Argument::any())->shouldNotBeCalled();

        $this->validate($value, $constraint);
    }

    public function it_adds_a_violation_if_the_url_is_localhost(
        ExecutionContextInterface $context,
        ConstraintViolationBuilderInterface $constraintViolationBuilder,
        ExternalUrl $constraint
    ): void {
        $this->initialize($context);

        $value = 'http://localhost/foo';

        $context->buildViolation($constraint->message)->willReturn($constraintViolationBuilder);
        $constraintViolationBuilder->setParameter(Argument::cetera())->willReturn($constraintViolationBuilder);
        $constraintViolationBuilder->addViolation()->shouldBeCalled();

        $this->validate($value, $constraint);
    }

    public function it_adds_a_violation_if_the_url_is_elasticsearch(
        ExecutionContextInterface $context,
        ConstraintViolationBuilderInterface $constraintViolationBuilder,
        ExternalUrl $constraint
    ): void {
        $this->initialize($context);

        $value = 'http://elasticsearch/foo';

        $context->buildViolation($constraint->message)->willReturn($constraintViolationBuilder);
        $constraintViolationBuilder->setParameter(Argument::cetera())->willReturn($constraintViolationBuilder);
        $constraintViolationBuilder->addViolation()->shouldBeCalled();

        $this->validate($value, $constraint);
    }

    public function it_adds_a_violation_if_the_url_is_memcached(
        ExecutionContextInterface $context,
        ConstraintViolationBuilderInterface $constraintViolationBuilder,
        ExternalUrl $constraint
    ): void {
        $this->initialize($context);

        $value = 'http://memcached/foo';

        $context->buildViolation($constraint->message)->willReturn($constraintViolationBuilder);
        $constraintViolationBuilder->setParameter(Argument::cetera())->willReturn($constraintViolationBuilder);
        $constraintViolationBuilder->addViolation()->shouldBeCalled();

        $this->validate($value, $constraint);
    }

    public function it_adds_a_violation_if_the_ip_is_in_private_range(
        ExecutionContextInterface $context,
        ConstraintViolationBuilderInterface $constraintViolationBuilder,
        DnsLookupInterface $dnsLookup,
        ExternalUrl $constraint
    ): void {
        $this->initialize($context);

        $value = 'http://akeneo.com/foo';

        $dnsLookup->ip('akeneo.com')->willReturn('172.16.0.1');
        $context->buildViolation($constraint->message)->willReturn($constraintViolationBuilder);
        $constraintViolationBuilder->setParameter(Argument::cetera())->willReturn($constraintViolationBuilder);
        $constraintViolationBuilder->addViolation()->shouldBeCalled();

        $this->validate($value, $constraint);
    }

    public function it_allows_the_ip_if_in_private_range_and_in_whitelist(
        ExecutionContextInterface $context,
        DnsLookupInterface $dnsLookup,
        ExternalUrl $constraint
    ): void {
        $this->beConstructedWith($dnsLookup, '172.16.0.0/24');
        $this->initialize($context);

        $value = 'http://akeneo.com/foo';

        $dnsLookup->ip('akeneo.com')->willReturn('172.16.0.1');
        $context->buildViolation(Argument::any())->shouldNotBeCalled();

        $this->validate($value, $constraint);
    }

    public function it_allows_the_ip_if_external(
        ExecutionContextInterface $context,
        DnsLookupInterface $dnsLookup,
        ExternalUrl $constraint
    ): void {
        $this->initialize($context);

        $value = 'http://akeneo.com/foo';

        $dnsLookup->ip('akeneo.com')->willReturn('168.212.226.204');
        $context->buildViolation(Argument::any())->shouldNotBeCalled();

        $this->validate($value, $constraint);
    }
}
