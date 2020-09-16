<?php
declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Application\Webhook\Command;

use Akeneo\Connectivity\Connection\Domain\Settings\Exception\ConstraintViolationListException;
use Akeneo\Connectivity\Connection\Domain\Webhook\Model\Write\ConnectionWebhook;
use Akeneo\Connectivity\Connection\Domain\Webhook\Persistence\Repository\ConnectionWebhookRepository;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class UpdateConnectionWebhookHandler
{
    /** @var ConnectionWebhookRepository */
    private $repository;

    /** @var ValidatorInterface */
    private $validator;

    public function __construct(ConnectionWebhookRepository $repository, ValidatorInterface $validator)
    {
        $this->repository = $repository;
        $this->validator = $validator;
    }

    public function handle(UpdateConnectionWebhookCommand $command): void
    {
        $webhook = new ConnectionWebhook($command->code(), $command->enabled(), $command->url());
        $violations = $this->validator->validate($webhook);
        if (0 !== $violations->count()) {
            throw new ConstraintViolationListException($violations);
        }
        $updated = $this->repository->update($webhook);
        if (!$updated) {
            throw new \RuntimeException();
        }
    }
}
