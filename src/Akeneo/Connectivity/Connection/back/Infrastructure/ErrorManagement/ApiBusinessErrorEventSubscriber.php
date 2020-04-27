<?php
declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\ErrorManagement;

use Akeneo\Connectivity\Connection\Domain\ErrorManagement\Persistence\Repository\BusinessErrorRepository;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\FlowType;
use Akeneo\Connectivity\Connection\Infrastructure\ConnectionContext;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ApiBusinessErrorEventSubscriber implements EventSubscriberInterface
{
    /** @var BusinessErrorRepository */
    private $repository;

    /** @var ConnectionContext */
    private $connectionContext;

    public function __construct(ConnectionContext $connectionContext, BusinessErrorRepository $repository)
    {
        $this->repository = $repository;
        $this->connectionContext = $connectionContext;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::RESPONSE => 'collectApiBusinessErrors'
        ];
    }

    public function collectApiBusinessErrors(ResponseEvent $responseEvent): void
    {
        if (!$this->isTheCollectOfBusinessErrorsNeeded($responseEvent)) {
            return;
        }

        $businessErrors = ExtractBusinessErrorsFromApiResponse::extractAll(
            $responseEvent->getResponse(),
            $this->connectionContext->getConnection()->code()
        );

        $this->repository->bulkInsert($businessErrors);
    }

    /**
     * We collect errors if:
     *     - The request is coming from product external API.
     *     - The response is not a StreamResponse because sub responses are launched internally and
     * we subscribe to them too.
     *     - The connection used to connect to the API is collectable.
     *     - The connection's flow type is source.
     *
     * @param ResponseEvent $event
     *
     * @return bool
     */
    private function isTheCollectOfBusinessErrorsNeeded(ResponseEvent $event): bool
    {
        $connection = $this->connectionContext->getConnection();
        $controller = $event->getRequest()->get('_controller');

        return !$event->getResponse() instanceof StreamedResponse &&
            null !== $connection &&
            in_array($controller, RoutesDictionary::API_PRODUCT) &&
            $this->connectionContext->isCollectable() &&
            FlowType::DATA_SOURCE === (string) $connection->flowType();
    }
}
