<?php
declare(strict_types=1);

namespace spec\Akeneo\Connectivity\Connection\Application\Webhook\Command;

use Akeneo\Connectivity\Connection\Application\Webhook\Command\UpdateWebhookCommand;
use Akeneo\Connectivity\Connection\Application\Webhook\Command\UpdateWebhookHandler;
use Akeneo\Connectivity\Connection\Domain\Settings\Exception\ConstraintViolationListException;
use Akeneo\Connectivity\Connection\Domain\ValueObject\Url;
use Akeneo\Connectivity\Connection\Domain\Webhook\Exception\ConnectionWebhookNotFoundException;
use Akeneo\Connectivity\Connection\Domain\Webhook\Model\Write\ConnectionWebhook;
use Akeneo\Connectivity\Connection\Domain\Webhook\Persistence\Repository\ConnectionWebhookRepository;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class UpdateWebhookHandlerSpec extends ObjectBehavior
{
    public function let(ConnectionWebhookRepository $repository, ValidatorInterface $validator): void
    {
        $this->beConstructedWith($repository, $validator);
    }

    public function it_is_an_update_webhook_handler(): void
    {
        $this->shouldHaveType(UpdateWebhookHandler::class);
    }

    public function it_updates_a_webhook_with_validated_data_from_the_command(
        $repository,
        $validator,
        ConstraintViolationListInterface $violationList
    ): void {
        $code = 'magento';
        $url = 'http://valid-url.com';
        $enabled = true;
        $command = new UpdateWebhookCommand($code, $enabled, $url);
        $isAValidWriteModel = function (ConnectionWebhook $webhook) use ($code, $enabled, $url) {
            return $webhook->code() === $code &&
                $webhook->enabled() === $enabled &&
                $webhook->url() instanceof Url &&
                (string) $webhook->url() === $url;
        };

        $validator->validate(Argument::that($isAValidWriteModel))->shouldBeCalled()->willReturn($violationList);
        $violationList->count()->willReturn(0);
        $repository->update(Argument::that($isAValidWriteModel))->shouldBeCalled()->willReturn(1);

        $this->handle($command);
    }

    /**
     * If a webhook is enabled, the URL can not be null.
     */
    public function it_does_not_update_a_webhook_with_invalid_data(
        $repository,
        $validator,
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

        $this
            ->shouldThrow(ConstraintViolationListException::class)
            ->during('handle', [$command]);
    }

    public function it_throws_a_domain_exception_if_the_update_fails(
        $repository,
        $validator,
        ConstraintViolationListInterface $violationList
    ): void {
        $code = 'a_connection_that_does_not_exist';
        $url = null;
        $enabled = false;
        $command = new UpdateWebhookCommand($code, $enabled, $url);
        $isAValidWriteModel = function (ConnectionWebhook $webhook) use ($code, $enabled, $url) {
            return $webhook->code() === $code &&
                $webhook->enabled() === $enabled &&
                $webhook->url() === $url;
        };

        $validator->validate(Argument::that($isAValidWriteModel))->shouldBeCalled()->willReturn($violationList);
        $violationList->count()->willReturn(0);
        $repository->update(Argument::that($isAValidWriteModel))->shouldBeCalled()->willReturn(0);

        $this
            ->shouldThrow(ConnectionWebhookNotFoundException::class)
            ->during('handle', [$command]);
    }
}
