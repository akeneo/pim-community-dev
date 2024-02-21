<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Controller\ExternalApi;

use Akeneo\Pim\Enrichment\Component\Product\Event\ProductDomainErrorEvent;
use Akeneo\Pim\Enrichment\Component\Product\Exception\UnknownProductException;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Remover\RemoverInterface;
use Oro\Bundle\SecurityBundle\SecurityFacade;
use Ramsey\Uuid\Uuid;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Controller to remove products using their Uuid
 *
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class DeleteProductByUuidController
{
    public function __construct(
        private ProductRepositoryInterface $productRepository,
        private EventDispatcherInterface $eventDispatcher,
        private SecurityFacade $security,
        private RemoverInterface $remover
    ) {
    }

    public function __invoke(string $uuid): Response
    {
        if (!$this->security->isGranted('pim_api_product_remove')) {
            throw new AccessDeniedHttpException('Access forbidden. You are not allowed to delete products');
        }

        $product = $this->productRepository->find(Uuid::fromString($uuid));

        if (null === $product) {
            $exception = new UnknownProductException($uuid);
            $this->eventDispatcher->dispatch(new ProductDomainErrorEvent($exception, null));

            throw new NotFoundHttpException($exception->getMessage(), $exception);
        }

        $this->remover->remove($product);

        return new Response(null, Response::HTTP_NO_CONTENT);
    }
}
