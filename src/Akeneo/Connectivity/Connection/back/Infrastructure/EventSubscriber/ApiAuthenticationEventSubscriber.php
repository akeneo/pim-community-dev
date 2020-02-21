<?php
declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\EventSubscriber;

use Akeneo\Connectivity\Connection\Domain\WrongCredentialsConnection\Model\Write\WrongCredentialsCombination;
use Akeneo\Connectivity\Connection\Domain\WrongCredentialsConnection\Persistence\Query\AreCredentialsValidCombinationQuery;
use Akeneo\Connectivity\Connection\Domain\WrongCredentialsConnection\Persistence\Query\SelectConnectionCodeByClientIdQuery;
use Akeneo\Connectivity\Connection\Domain\WrongCredentialsConnection\Persistence\Repository\WrongCredentialsCombinationRepository;
use Akeneo\Connectivity\Connection\Infrastructure\Persistence\Dbal\Query\DbalSelectConnectionCodeByClientIdQuery;
use Akeneo\Tool\Bundle\ApiBundle\EventSubscriber\ApiAuthenticationEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ApiAuthenticationEventSubscriber implements EventSubscriberInterface
{
    /** @var AreCredentialsValidCombinationQuery */
    private $areCredentialsValidCombination;

    /** @var DbalSelectConnectionCodeByClientIdQuery */
    private $selectConnectionCode;

    /** @var WrongCredentialsCombinationRepository */
    private $repository;

    public function __construct(
        AreCredentialsValidCombinationQuery $areCredentialsValidCombination,
        SelectConnectionCodeByClientIdQuery $selectConnectionCode,
        WrongCredentialsCombinationRepository $repository
    ) {
        $this->areCredentialsValidCombination = $areCredentialsValidCombination;
        $this->selectConnectionCode = $selectConnectionCode;
        $this->repository = $repository;
    }

    public static function getSubscribedEvents(): array
    {
        return [ApiAuthenticationEvent::class => 'checkCredentialsCombination'];
    }

    public function checkCredentialsCombination(ApiAuthenticationEvent $event): void
    {
        if ($this->areCredentialsValidCombination->execute($event->clientId(), $event->username())) {
            return;
        }

        $connectionCode = $this->selectConnectionCode->execute($event->clientId());
        $this->repository->create(new WrongCredentialsCombination($connectionCode, $event->username()));
    }
}
