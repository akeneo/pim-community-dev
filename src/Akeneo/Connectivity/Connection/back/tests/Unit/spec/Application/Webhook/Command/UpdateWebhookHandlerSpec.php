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
use Akeneo\Connectivity\Connection\Domain\Webhook\Persistence\Query\SelectWebhookSecretQuery;
use Akeneo\Connectivity\Connection\Domain\Webhook\Persistence\Repository\ConnectionWebhookRepository;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class UpdateWebhookHandlerSpec extends ObjectBehavior
{
    public function let(
        ConnectionWebhookRepository $repository,
        ValidatorInterface $validator,
        SelectWebhookSecretQuery $selectWehbookSecretQuery,
        GenerateWebhookSecretHandler $generateWebhookSecretHandler
    ): void {
        $this->beConstructedWith($repository, $validator, $selectWehbookSecretQuery, $generateWebhookSecretHandler);
    }

    public function it_is_an_update_webhook_handler(): void
    {
        $this->shouldHaveType(UpdateWebhookHandler::class);
    }

    public function it_updates_a_webhook_and_create_a_secret_with_validated_data(
        $repository,
        $validator,
        $selectWehbookSecretQuery,
        $generateWebhookSecretHandler,
        ConstraintViolationListInterface $violationList
    ): void {
        $code = 'magento';
        $url = 'http://valid-url.com';
        $enabled = true;
        $secret = 'secret';
        $command = new UpdateWebhookCommand($code, $enabled, $url);
        $isAValidWriteModel = function (ConnectionWebhook $webhook) use ($code, $enabled, $url) {
            return $webhook->code() === $code &&
                $webhook->enabled() === $enabled &&
                $webhook->url() instanceof Url &&
                (string) $webhook->url() === $url;
        };

        $validator->validate(Argument::that($isAValidWriteModel))->shouldBeCalled()->willReturn($violationList);
        $violationList->count()->willReturn(0);
        $repository->update(Argument::that($isAValidWriteModel))->shouldBeCalled();

        $selectWehbookSecretQuery->execute($code)->willReturn(null);
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
        $repository,
        $validator,
        $selectWehbookSecretQuery,
        $generateWebhookSecretHandler,
        ConstraintViolationListInterface $violationList
    ): void {
        $code = 'magento';
        $url = 'http://valid-url.com';
        $enabled = true;
        $secret = 'secret';
        $command = new UpdateWebhookCommand($code, $enabled, $url);
        $isAValidWriteModel = function (ConnectionWebhook $webhook) use ($code, $enabled, $url) {
            return $webhook->code() === $code &&
                $webhook->enabled() === $enabled &&
                $webhook->url() instanceof Url &&
                (string) $webhook->url() === $url;
        };

        $validator->validate(Argument::that($isAValidWriteModel))->shouldBeCalled()->willReturn($violationList);
        $violationList->count()->willReturn(0);
        $repository->update(Argument::that($isAValidWriteModel))->shouldBeCalled();

        $selectWehbookSecretQuery->execute($code)->willReturn($secret);
        $generateWebhookSecretHandler->handle(Argument::cetera())->shouldNotBeCalled();

        $this->handle($command);
    }

    /**
     * If a webhook is enabled, the URL can not be null.
     */
    public function it_does_not_update_a_webhook_with_invalid_data(
        $repository,
        $validator,
        $generateWebhookSecretHandler,
        ConstraintViolationListInterface $violationList
    ): void {
        $code = 'magento';
        $enabled = true;
        $url = null;
        $command = new UpdateWebhookCommand($code, $enabled, $url);
        $isAValidWriteModel = function (ConnectionWebhook $webhook) use ($code, $enabled, $url) {
            return $webhook->code() === $code &&
                $webhook->enabled() === $enabled &&
                $webhook->url() === null;
        };

        $validator->validate(Argument::that($isAValidWriteModel))->shouldBeCalled()->willReturn($violationList);
        $violationList->count()->willReturn(1);
        $repository->update(Argument::cetera())->shouldNotBeCalled();
        $generateWebhookSecretHandler->handle(Argument::cetera())->shouldNotBeCalled();

        $this
            ->shouldThrow(ConstraintViolationListException::class)
            ->during('handle', [$command]);
    }
}
