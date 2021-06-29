<?php

declare(strict_types=1);

namespace spec\Akeneo\Connectivity\Connection\Application\Webhook\Validation;

use Akeneo\Connectivity\Connection\Application\Webhook\Validation\EventSubscriptionsLimit;
use Akeneo\Connectivity\Connection\Application\Webhook\Validation\EventSubscriptionsLimitValidator;
use Akeneo\Connectivity\Connection\Domain\Webhook\Model\Read\ActiveWebhook;
use Akeneo\Connectivity\Connection\Domain\Webhook\Model\Write\ConnectionWebhook;
use Akeneo\Connectivity\Connection\Domain\Webhook\Persistence\Query\SelectActiveWebhooksQuery;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidatorInterface;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class EventSubscriptionsLimitValidatorSpec extends ObjectBehavior
{
    const ACTIVE_EVENT_SUBSCRIPTIONS_LIMIT = 3;

    public function let(SelectActiveWebhooksQuery $selectActiveWebhooksQuery, ExecutionContextInterface $context): void
    {
        $this->beConstructedWith($selectActiveWebhooksQuery, self::ACTIVE_EVENT_SUBSCRIPTIONS_LIMIT);
        $this->initialize($context);
    }

    public function it_is_initializable(): void
    {
        $this->shouldBeAnInstanceOf(EventSubscriptionsLimitValidator::class);
    }

    public function it_is_a_constraint_validator(): void
    {
        $this->shouldImplement(ConstraintValidatorInterface::class);
    }

    public function it_supports_the_event_subscriptions_limit_constraint($selectActiveWebhooksQuery): void
    {
        $selectActiveWebhooksQuery->execute()->willReturn([]);

        $eventSubscription = new ConnectionWebhook('erp', true, 'http://localhost');
        $constraint = new EventSubscriptionsLimit();

        $this
            ->shouldNotThrow(new UnexpectedTypeException($constraint, EventSubscriptionsLimit::class))
            ->during('validate', [$eventSubscription, $constraint]);
    }

    public function it_does_not_support_other_constraints(Constraint $constraint): void
    {
        $eventSubscription = new ConnectionWebhook('erp', true, 'http://localhost');

        $this
            ->shouldThrow(new UnexpectedTypeException($constraint->getWrappedObject(), EventSubscriptionsLimit::class))
            ->during('validate', [$eventSubscription, $constraint]);
    }

    public function it_supports_the_event_subscription_value($selectActiveWebhooksQuery): void
    {
        $selectActiveWebhooksQuery->execute()->willReturn([]);

        $eventSubscription = new ConnectionWebhook('erp', true, 'http://localhost');
        $constraint = new EventSubscriptionsLimit();

        $this
            ->shouldNotThrow(new UnexpectedValueException($eventSubscription, ConnectionWebhook::class))
            ->during('validate', [$eventSubscription, $constraint]);
    }

    public function it_does_not_support_other_values(): void
    {
        $value = new \stdClass();
        $constraint = new EventSubscriptionsLimit();

        $this
            ->shouldThrow(new UnexpectedValueException($value, ConnectionWebhook::class))
            ->during('validate', [$value, $constraint]);
    }

    public function it_does_not_check_the_limit_if_the_event_subscription_is_disabled($selectActiveWebhooksQuery): void
    {
        $selectActiveWebhooksQuery->execute()->shouldNotBeCalled();

        $eventSubscription = new ConnectionWebhook('erp', false, null);
        $constraint = new EventSubscriptionsLimit();

        $this->validate($eventSubscription, $constraint);
    }

    public function it_adds_a_violation_if_the_limit_is_reached(
        $selectActiveWebhooksQuery,
        $context,
        ConstraintViolationBuilderInterface $constraintViolationBuilder
    ): void {
        $selectActiveWebhooksQuery->execute()->willReturn([
            new ActiveWebhook('dam', 1, 'secret', 'http://localhost'),
            new ActiveWebhook('ecommerce', 1, 'secret', 'http://localhost'),
            new ActiveWebhook('translations', 1, 'secret', 'http://localhost'),
        ]);

        $eventSubscription = new ConnectionWebhook('erp', true, 'http://localhost');
        $constraint = new EventSubscriptionsLimit();

        $context->buildViolation($constraint->message)->willReturn($constraintViolationBuilder);
        $constraintViolationBuilder->atPath('enabled')->willReturn($constraintViolationBuilder);
        $constraintViolationBuilder->addViolation()->shouldBeCalled();

        $this->validate($eventSubscription, $constraint);
    }

    public function it_does_not_count_itself_in_the_limit_check($selectActiveWebhooksQuery, $context): void
    {
        $selectActiveWebhooksQuery->execute()->willReturn([
            new ActiveWebhook('dam', 1, 'secret', 'http://localhost'),
            new ActiveWebhook('ecommerce', 1, 'secret', 'http://localhost'),
            new ActiveWebhook('erp', 1, 'secret', 'http://localhost'),
        ]);

        $eventSubscription = new ConnectionWebhook('erp', true, 'http://localhost');
        $constraint = new EventSubscriptionsLimit();

        $context->buildViolation($constraint->message)->shouldNotBeCalled();

        $this->validate($eventSubscription, $constraint);
    }
}
