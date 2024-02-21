<?php
declare(strict_types=1);

namespace spec\Akeneo\Connectivity\Connection\Application\Webhook\Validation;

use Akeneo\Connectivity\Connection\Application\Webhook\Validation\EnabledWebhookRequiresAnUrl;
use Akeneo\Connectivity\Connection\Application\Webhook\Validation\EnabledWebhookRequiresAnUrlValidator;
use Akeneo\Connectivity\Connection\Domain\Webhook\Model\Write\ConnectionWebhook;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidatorInterface;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class EnabledWebhookRequiresAnUrlValidatorSpec extends ObjectBehavior
{
    public function let(ExecutionContextInterface $context): void
    {
        $this->initialize($context);
    }

    public function it_is_an_enabled_webhook_requires_an_url_constraint_validator(): void
    {
        $this->shouldHaveType(EnabledWebhookRequiresAnUrlValidator::class);
        $this->shouldImplement(ConstraintValidatorInterface::class);
    }

    public function it_validates_an_enabled_webhook_with_an_url($context): void
    {
        $constraint = new EnabledWebhookRequiresAnUrl();
        $webhook = new ConnectionWebhook('magento', true, 'http://valid-url.com');

        $context->buildViolation(Argument::cetera())->shouldNotBeCalled();

        $this->validate($webhook, $constraint);
    }

    public function it_validates_a_disabled_webhook_with_an_url($context): void
    {
        $constraint = new EnabledWebhookRequiresAnUrl();
        $webhook = new ConnectionWebhook('magento', false, 'http://valid-url.com');

        $context->buildViolation(Argument::cetera())->shouldNotBeCalled();

        $this->validate($webhook, $constraint);
    }

    public function it_validates_a_disabled_webhook_with_an_empty_url($context): void
    {
        $constraint = new EnabledWebhookRequiresAnUrl();
        $webhook = new ConnectionWebhook('magento', false, '');

        $context->buildViolation(Argument::cetera())->shouldNotBeCalled();

        $this->validate($webhook, $constraint);
    }

    public function it_validates_a_disabled_webhook_with_a_null_url($context): void
    {
        $constraint = new EnabledWebhookRequiresAnUrl();
        $webhook = new ConnectionWebhook('magento', false);

        $context->buildViolation(Argument::cetera())->shouldNotBeCalled();

        $this->validate($webhook, $constraint);
    }

    /**
     * Url format is validated with Url validator from symfony
     */
    public function it_validates_an_enabled_webhook_with_an_invalid_url($context): void
    {
        $constraint = new EnabledWebhookRequiresAnUrl();
        $webhook = new ConnectionWebhook('magento', true, 'not_a_url');

        $context->buildViolation(Argument::cetera())->shouldNotBeCalled();

        $this->validate($webhook, $constraint);
    }

    public function it_does_not_validate_an_enabled_webhook_with_a_null_url(
        $context,
        ConstraintViolationBuilderInterface $constraintViolationBuilder
    ): void {
        $constraint = new EnabledWebhookRequiresAnUrl();
        $webhook = new ConnectionWebhook('magento', true);

        $context->buildViolation($constraint->message)->shouldBeCalled()->willReturn($constraintViolationBuilder);
        $constraintViolationBuilder->atPath('url')->shouldBeCalled()->willReturn($constraintViolationBuilder);
        $constraintViolationBuilder->addViolation()->shouldBeCalled();

        $this->validate($webhook, $constraint);
    }

    public function it_does_not_validate_an_enabled_webhook_with_an_empty_url(
        $context,
        ConstraintViolationBuilderInterface $constraintViolationBuilder
    ): void {
        $constraint = new EnabledWebhookRequiresAnUrl();
        $webhook = new ConnectionWebhook('magento', true, '');

        $context->buildViolation($constraint->message)->shouldBeCalled()->willReturn($constraintViolationBuilder);
        $constraintViolationBuilder->atPath('url')->shouldBeCalled()->willReturn($constraintViolationBuilder);
        $constraintViolationBuilder->addViolation()->shouldBeCalled();

        $this->validate($webhook, $constraint);
    }

    public function it_throws_an_exception_if_the_given_constraint_is_not_the_good_one(): void
    {
        $this
            ->shouldThrow(UnexpectedTypeException::class)
            ->during(
                'validate',
                [
                    new ConnectionWebhook('magento', false),
                    new LocalConstraint()
                ]
            );
    }

    public function it_throws_an_exception_if_the_data_to_validate_is_not_connection_webhook_write_model(): void
    {
        $this
            ->shouldThrow(UnexpectedTypeException::class)
            ->during(
                'validate',
                [
                    'a_webhook',
                    new EnabledWebhookRequiresAnUrl()
                ]
            );
    }
}

class LocalConstraint extends Constraint
{
}
