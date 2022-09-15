<?php

declare(strict_types=1);

namespace spec\Akeneo\Connectivity\Connection\Application\Webhook\Command;

use Akeneo\Connectivity\Connection\Application\Webhook\Command\GenerateWebhookSecretCommand;
use Akeneo\Connectivity\Connection\Application\Webhook\Command\GenerateWebhookSecretHandler;
use Akeneo\Connectivity\Connection\Application\Webhook\Command\UpdateWebhookCommand;
use Akeneo\Connectivity\Connection\Application\Webhook\Command\UpdateWebhookHandler;
use Akeneo\Connectivity\Connection\Domain\Settings\Exception\ConstraintViolationListException;
use Akeneo\Connectivity\Connection\Domain\ValueObject\Url;
use Akeneo\Connectivity\Connection\Domain\Webhook\Model\Write\ConnectionWebhook;
use Akeneo\Connectivity\Connection\Domain\Webhook\Persistence\Query\SelectWebhookSecretQueryInterface;
use Akeneo\Connectivity\Connection\Domain\Webhook\Persistence\Query\UpdateConnectionWebhookQueryInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class UpdateWebhookHandlerSpec extends ObjectBehavior
{
    public function let(
        UpdateConnectionWebhookQueryInterface $updateConnectionWebhookQuery,
        ValidatorInterface $validator,
        SelectWebhookSecretQueryInterface $selectWebhookSecretQuery,
        GenerateWebhookSecretHandler $generateWebhookSecretHandler
    ): void {
        $this->beConstructedWith(
            $updateConnectionWebhookQuery,
            $validator,
            $selectWebhookSecretQuery,
            $generateWebhookSecretHandler
        );
    }

    public function it_is_an_update_webhook_handler(): void
    {
        $this->shouldHaveType(UpdateWebhookHandler::class);
    }

    public function it_updates_a_webhook_and_create_a_secret_with_validated_data(
        $updateConnectionWebhookQuery,
        $validator,
        $selectWebhookSecretQuery,
        $generateWebhookSecretHandler,
        ConstraintViolationListInterface $violationList
    ): void {
        $code = 'magento';
        $url = 'http://valid-url.com';
        $enabled = true;
        $isUsingUuid = true;
        $secret = 'secret';
        $command = new UpdateWebhookCommand($code, $enabled, $url, $isUsingUuid);
        $isAValidWriteModel = function (ConnectionWebhook $webhook) use ($code, $enabled, $url, $isUsingUuid) {
            return $webhook->code() === $code &&
                $webhook->enabled() === $enabled &&
                $webhook->isUsingUuid() === $isUsingUuid &&
                $webhook->url() instanceof Url &&
                (string) $webhook->url() === $url;
        };

        $validator->validate(Argument::that($isAValidWriteModel))->shouldBeCalled()->willReturn($violationList);
        $violationList->count()->willReturn(0);
        $updateConnectionWebhookQuery->execute(Argument::that($isAValidWriteModel))->shouldBeCalled();

        $selectWebhookSecretQuery->execute($code)->willReturn(null);
        $generateWebhookSecretHandler
            ->handle(
                Argument::that(function (GenerateWebhookSecretCommand $command) use ($code) {
                    return $command->connectionCode() === $code;
                })
            )
            ->shouldBeCalled()
            ->willReturn($secret);

        $this->handle($command);
    }

    public function it_updates_a_webhook_with_validated_data(
        $updateConnectionWebhookQuery,
        $validator,
        $selectWebhookSecretQuery,
        $generateWebhookSecretHandler,
        ConstraintViolationListInterface $violationList
    ): void {
        $code = 'magento';
        $url = 'http://valid-url.com';
        $enabled = true;
        $isUsingUuid = true;
        $secret = 'secret';
        $command = new UpdateWebhookCommand($code, $enabled, $url, $isUsingUuid);
        $isAValidWriteModel = function (ConnectionWebhook $webhook) use ($code, $enabled, $url, $isUsingUuid) {
            return $webhook->code() === $code &&
                $webhook->enabled() === $enabled &&
                $webhook->isUsingUuid() === $isUsingUuid &&
                $webhook->url() instanceof Url &&
                (string) $webhook->url() === $url;
        };

        $validator->validate(Argument::that($isAValidWriteModel))->shouldBeCalled()->willReturn($violationList);
        $violationList->count()->willReturn(0);
        $updateConnectionWebhookQuery->execute(Argument::that($isAValidWriteModel))->shouldBeCalled();

        $selectWebhookSecretQuery->execute($code)->willReturn($secret);
        $generateWebhookSecretHandler->handle(Argument::cetera())->shouldNotBeCalled();

        $this->handle($command);
    }

    /**
     * If a webhook is enabled, the URL can not be null.
     */
    public function it_does_not_update_a_webhook_with_invalid_data(
        $updateConnectionWebhookQuery,
        $validator,
        $generateWebhookSecretHandler,
        ConstraintViolationListInterface $violationList
    ): void {
        $code = 'magento';
        $enabled = true;
        $isUsingUuid = true;
        $url = null;
        $command = new UpdateWebhookCommand($code, $enabled, $url, $isUsingUuid);
        $isAValidWriteModel = function (ConnectionWebhook $webhook) use ($code, $enabled, $url, $isUsingUuid) {
            return $webhook->code() === $code &&
                $webhook->enabled() === $enabled &&
                $webhook->isUsingUuid() === $isUsingUuid &&
                $webhook->url() === null;
        };

        $validator->validate(Argument::that($isAValidWriteModel))->shouldBeCalled()->willReturn($violationList);
        $violationList->count()->willReturn(1);
        $updateConnectionWebhookQuery->execute(Argument::cetera())->shouldNotBeCalled();
        $generateWebhookSecretHandler->handle(Argument::cetera())->shouldNotBeCalled();

        $this
            ->shouldThrow(ConstraintViolationListException::class)
            ->during('handle', [$command]);
    }
}
