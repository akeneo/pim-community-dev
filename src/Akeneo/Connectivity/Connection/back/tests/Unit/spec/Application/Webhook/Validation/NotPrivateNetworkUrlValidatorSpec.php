<?php

declare(strict_types=1);

namespace spec\Akeneo\Connectivity\Connection\Application\Webhook\Validation;

use Akeneo\Connectivity\Connection\Application\Webhook\Validation\NotPrivateNetworkUrl;
use Akeneo\Connectivity\Connection\Application\Webhook\Validation\NotPrivateNetworkUrlValidator;
use Akeneo\Connectivity\Connection\Infrastructure\Service\DnsLookup\FakeDnsLookup;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class NotPrivateNetworkUrlValidatorSpec extends ObjectBehavior
{
    public function let(): void
    {
        $this->beConstructedWith(new FakeDnsLookup(['8.8.8.8']));
    }

    public function it_is_initializable(): void
    {
        $this->shouldBeAnInstanceOf(NotPrivateNetworkUrlValidator::class);
    }

    public function it_expects_not_private_network_url_constraint()
    {
        $constraint = new class() extends Constraint
        {
        };

        $this
            ->shouldThrow(UnexpectedTypeException::class)
            ->during('validate', [null, $constraint]);
    }

    public function it_validates_null()
    {
        $this->validate(null, new NotPrivateNetworkUrl());
    }

    public function it_validates_empty_string()
    {
        $this->validate('', new NotPrivateNetworkUrl());
    }

    public function it_validates_empty_string_from_object()
    {
        $url = new class()
        {
            public function __toString(): string
            {
                return '';
            }
        };


        $this->validate($url, new NotPrivateNetworkUrl());
    }

    public function it_expects_string_compatible_type()
    {
        $url = new \stdClass();
        $this
            ->shouldThrow(UnexpectedValueException::class)
            ->during('validate', [$url, new NotPrivateNetworkUrl()]);
    }

    public function it_does_not_validate_unresolvable_url(
        ExecutionContextInterface $context,
        ConstraintViolationBuilderInterface $builder
    ) {
        $this->beConstructedWith(new FakeDnsLookup());
        $this->initialize($context);

        $constraint = new NotPrivateNetworkUrl();
        $context->buildViolation($constraint->unresolvableHostMessage)
            ->willReturn($builder);
        $builder->setParameter('{{ host }}', '"unresolvable-url.dev"')
            ->willReturn($builder);
        $builder->addViolation()
            ->shouldBeCalled();

        $url = 'https://unresolvable-url.dev/';

        $this->validate($url, $constraint);
    }

    public function it_validates_public_network_url()
    {
        $url = 'https://public-network-url.dev/';

        $this->validate($url, new NotPrivateNetworkUrl());
    }

    public function it_does_not_validate_private_network_url(
        ExecutionContextInterface $context,
        ConstraintViolationBuilderInterface $builder
    ) {
        $this->beConstructedWith(new FakeDnsLookup(['127.0.0.1']));
        $this->initialize($context);

        $constraint = new NotPrivateNetworkUrl();
        $context->buildViolation($constraint->ipBlockedMessage)
            ->willReturn($builder);
        $builder->setParameter('{{ ip }}', '"127.0.0.1"')
            ->willReturn($builder);
        $builder->setParameter('{{ url }}', '"https://private-network-url.dev/"')
            ->willReturn($builder);
        $builder->addViolation()
            ->shouldBeCalled();

        $url = 'https://private-network-url.dev/';

        $this->validate($url, $constraint);
    }
}
