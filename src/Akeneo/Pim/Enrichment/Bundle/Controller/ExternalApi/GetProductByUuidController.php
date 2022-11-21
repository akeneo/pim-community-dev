<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Controller\ExternalApi;

use Akeneo\Pim\Enrichment\Component\Product\Connector\UseCase\GetProductsWithCompletenessesInterface;
use Akeneo\Pim\Enrichment\Component\Product\Connector\UseCase\GetProductsWithQualityScoresInterface;
use Akeneo\Pim\Enrichment\Component\Product\Event\Connector\ReadProductsEvent;
use Akeneo\Pim\Enrichment\Component\Product\Exception\ObjectNotFoundException;
use Akeneo\Pim\Enrichment\Component\Product\Normalizer\ExternalApi\ConnectorProductWithUuidNormalizer;
use Akeneo\Pim\Enrichment\Component\Product\Query\GetConnectorProducts;
use Akeneo\UserManagement\Component\Model\UserInterface;
use Oro\Bundle\SecurityBundle\SecurityFacade;
use Ramsey\Uuid\Uuid;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Webmozart\Assert\Assert;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GetProductByUuidController
{
    public function __construct(
        private ConnectorProductWithUuidNormalizer $connectorProductWithUuidNormalizer,
        private TokenStorageInterface $tokenStorage,
        private GetConnectorProducts $getConnectorProducts,
        private GetConnectorProducts $getConnectorProductsWithOptions,
        private EventDispatcherInterface $eventDispatcher,
        private GetProductsWithQualityScoresInterface $getProductsWithQualityScores,
        private GetProductsWithCompletenessesInterface $getProductsWithCompletenesses,
        private SecurityFacade $security
    ) {
    }

    public function __invoke(Request $request, string $uuid): JsonResponse
    {
        if (!$this->security->isGranted('pim_api_product_list')) {
            throw new AccessDeniedHttpException('Access forbidden. You are not allowed to list products.');
        }

        $connectorProductsQuery = 'true' === $request->query->get('with_attribute_options', 'false') ?
            $this->getConnectorProductsWithOptions :
            $this->getConnectorProducts;

        try {
            $user = $this->tokenStorage->getToken()->getUser();
            Assert::isInstanceOf($user, UserInterface::class);

            $product = $connectorProductsQuery->fromProductUuid(Uuid::fromString($uuid), $user->getId());
            $this->eventDispatcher->dispatch(new ReadProductsEvent(1));

            if ($request->query->getAlpha('with_quality_scores', 'false') === 'true') {
                $product = $this->getProductsWithQualityScores->fromConnectorProduct($product);
            }
            if ($request->query->getAlpha('with_completenesses', 'false') === 'true') {
                $product = $this->getProductsWithCompletenesses->fromConnectorProduct($product);
            }
        } catch (ObjectNotFoundException) {
            throw new NotFoundHttpException(sprintf('Product "%s" does not exist or you do not have permission to access it.', $uuid));
        }

        $normalizedProduct = $this->connectorProductWithUuidNormalizer->normalizeConnectorProduct($product);

        return new JsonResponse($normalizedProduct);
    }
}
