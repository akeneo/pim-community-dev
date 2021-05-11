<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\EventSubscriber;

use Akeneo\Connectivity\Connection\Application\ConnectionContextInterface;
use Akeneo\Connectivity\Connection\Domain\WrongCredentialsConnection\Model\Write\WrongCredentialsCombination;
use Akeneo\Connectivity\Connection\Domain\WrongCredentialsConnection\Persistence\Repository\WrongCredentialsCombinationRepository;
use Akeneo\Tool\Bundle\ApiBundle\EventSubscriber\ApiAuthenticationEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ApiAuthenticationEventSubscriber implements EventSubscriberInterface
{
    private ConnectionContextInterface $connectionContext;

    private WrongCredentialsCombinationRepository $repository;

    public function __construct(
        ConnectionContextInterface $connectionContext,
        WrongCredentialsCombinationRepository $repository
    ) {
        $this->connectionContext = $connectionContext;
        $this->repository = $repository;
    }

    /**
     * @return array<class-string<ApiAuthenticationEvent>, string>
     */
    public static function getSubscribedEvents(): array
    {
        return [ApiAuthenticationEvent::class => 'checkCredentialsCombination'];
    }

    public function checkCredentialsCombination(ApiAuthenticationEvent $event): void
    {
        if ($this->connectionContext->areCredentialsValidCombination()) {
            return;
        }

        if (null === $this->connectionContext->getConnection()) {
            return;
        }

        $this->repository->create(new WrongCredentialsCombination(
            (string) $this->connectionContext->getConnection()->code(),
            $event->username()
        ));
    }
}
