<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Application\Webhook\Command;

use Akeneo\Connectivity\Connection\Domain\Settings\Exception\ConstraintViolationListException;
use Akeneo\Connectivity\Connection\Domain\Webhook\Model\Write\ConnectionWebhook;
use Akeneo\Connectivity\Connection\Domain\Webhook\Persistence\Query\SelectWebhookSecretQueryInterface;
use Akeneo\Connectivity\Connection\Domain\Webhook\Persistence\Repository\ConnectionWebhookRepositoryInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class UpdateWebhookHandler
{
    private ConnectionWebhookRepositoryInterface $repository;

    private ValidatorInterface $validator;

    private SelectWebhookSecretQueryInterface $selectWebhookSecretQuery;

    private GenerateWebhookSecretHandler $generateWebhookSecretHandler;

    public function __construct(
        ConnectionWebhookRepositoryInterface $repository,
        ValidatorInterface $validator,
        SelectWebhookSecretQueryInterface $selectWebhookSecretQuery,
        GenerateWebhookSecretHandler $generateWebhookSecretHandler
    ) {
        $this->repository = $repository;
        $this->validator = $validator;
        $this->selectWebhookSecretQuery = $selectWebhookSecretQuery;
        $this->generateWebhookSecretHandler = $generateWebhookSecretHandler;
    }

    public function handle(UpdateWebhookCommand $command): void
    {
        $connectionCode = $command->code();

        $webhook = new ConnectionWebhook($connectionCode, $command->enabled(), $command->url());

        $violations = $this->validator->validate($webhook);
        if (0 !== $violations->count()) {
            throw new ConstraintViolationListException($violations);
        }

        $this->repository->update($webhook);

        $secret = $this->selectWebhookSecretQuery->execute($connectionCode);
        if (null === $secret) {
            $this->generateWebhookSecretHandler->handle(
                new GenerateWebhookSecretCommand($connectionCode),
            );
        }
    }
}
