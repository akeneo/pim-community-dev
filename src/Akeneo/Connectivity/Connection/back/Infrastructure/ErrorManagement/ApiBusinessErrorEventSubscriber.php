<?php
declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\ErrorManagement;

use Akeneo\Connectivity\Connection\Domain\ErrorManagement\Persistence\Repository\BusinessErrorRepository;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class ApiBusinessErrorEventSubscriber implements EventSubscriberInterface
{
    /** @var BusinessErrorRepository */
    private $repository;

    public function __construct(BusinessErrorRepository $repository)
    {
        $this->repository = $repository;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::RESPONSE => 'collectApiBusinessErrors'
        ];
    }

    public function collectApiBusinessErrors(ResponseEvent $responseEvent): void
    {
        $request = $responseEvent->getRequest();
        if (!in_array($request->get('_route'), RoutesDictionary::API_PRODUCT)) {
            return;
        }
        $response = $responseEvent->getResponse();
        $extractBusinessErrors = new ExtractBusinessErrorsFromApiResponse();

        $businessErrors = $extractBusinessErrors->extractAll($response, 'erp');
        $this->repository->bulkInsert($businessErrors);
    }
}
