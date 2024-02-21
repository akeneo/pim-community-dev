<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Controller\ExternalApi;

use Akeneo\Pim\Enrichment\Bundle\EventSubscriber\Product\OnSave\ApiAggregatorForProductPostSaveEventSubscriber;
use Akeneo\Tool\Bundle\ApiBundle\Cache\WarmupQueryCache;
use Akeneo\Tool\Bundle\ApiBundle\Stream\StreamResourceResponse;
use Oro\Bundle\SecurityBundle\SecurityFacade;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class PartialUpdateListProductsByUuidController
{
    public function __construct(
        private SecurityFacade $security,
        private WarmupQueryCache $warmupQueryCache,
        private ApiAggregatorForProductPostSaveEventSubscriber $apiAggregatorForProductPostSave,
        private StreamResourceResponse $partialUpdateStreamResource,
        private LoggerInterface $logger,
    ) {
    }

    public function __invoke(Request $request): Response
    {
        if (!$this->security->isGranted('pim_api_product_edit')) {
            throw new AccessDeniedHttpException('Access forbidden. You are not allowed to create or update products.');
        }

        $this->warmupQueryCache->fromRequest($request);
        $resource = $request->getContent(true);
        $this->apiAggregatorForProductPostSave->activate();

        return $this->partialUpdateStreamResource->streamResponse($resource, [], function () {
            try {
                $this->apiAggregatorForProductPostSave->dispatchAllEvents();
            } catch (\Throwable $exception) {
                $this->logger->warning('An exception has been thrown in the post-save events', [
                    'exception' => $exception,
                ]);
            }
            $this->apiAggregatorForProductPostSave->deactivate();
        });
    }
}
