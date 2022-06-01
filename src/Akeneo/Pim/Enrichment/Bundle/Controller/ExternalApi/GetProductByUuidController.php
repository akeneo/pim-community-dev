<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Controller\ExternalApi;

use Akeneo\Pim\Enrichment\Bundle\Storage\Sql\Connector\Uuid\SqlGetConnectorProducts;
use Akeneo\Pim\Enrichment\Bundle\Storage\Sql\Connector\Uuid\SqlGetConnectorProductsWithOptions;
use Akeneo\Pim\Enrichment\Component\Product\Connector\UseCase\Uuid\GetProductsWithCompletenesses;
use Akeneo\Pim\Enrichment\Component\Product\Connector\UseCase\Uuid\GetProductsWithQualityScores;
use Akeneo\Pim\Enrichment\Component\Product\Event\Connector\ReadProductsEvent;
use Akeneo\Pim\Enrichment\Component\Product\Exception\ObjectNotFoundException;
use Akeneo\Pim\Enrichment\Component\Product\Normalizer\ExternalApi\Uuid\ConnectorProductNormalizer;
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
 * @author    Adrien Migaire <adrien.migaire@akeneo.com>
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class GetProductByUuidController
{
    public function __construct(
        private ConnectorProductNormalizer $connectorProductNormalizer,
        private TokenStorageInterface $tokenStorage,
        private SqlGetConnectorProducts $getConnectorProducts,
        private SqlGetConnectorProductsWithOptions $getConnectorProductsWithOptions,
        private EventDispatcherInterface $eventDispatcher,
        private GetProductsWithQualityScores $getProductsWithQualityScores,
        private GetProductsWithCompletenesses $getProductsWithCompletenesses,
        private SecurityFacade $security,
    ) {
    }

    /**
     * @param Request $request
     * @param string $uuid
     * @return JsonResponse
     */
    public function getAction(Request $request, string $uuid): JsonResponse
    {
        $this->denyAccessUnlessAclIsGranted('pim_api_product_list');

        $productUuid = Uuid::fromString($uuid);

        $connectorProductsQuery = 'true' === $request->query->get('with_attribute_options', "false") ?
            $this->getConnectorProductsWithOptions :
            $this->getConnectorProducts;

        try {
            $user = $this->tokenStorage->getToken()->getUser();
            Assert::isInstanceOf($user, UserInterface::class);

            $product = $connectorProductsQuery->fromProductUuid($productUuid, $user->getId());
            $this->eventDispatcher->dispatch(new ReadProductsEvent(1));

            if ($request->query->getAlpha('with_quality_scores', 'false') === 'true') {
                $product = $this->getProductsWithQualityScores->fromConnectorProduct($product);
            }
            if ($request->query->getAlpha('with_completenesses', 'false') === 'true') {
                $product = $this->getProductsWithCompletenesses->fromConnectorProduct($product);
            }
        } catch (ObjectNotFoundException $e) {
            throw new NotFoundHttpException(sprintf('Product "%s" does not exist or you do not have permission to access it.', $productUuid->toString()));
        }

        $normalizedProduct = $this->connectorProductNormalizer->normalizeConnectorProduct($product);

        return new JsonResponse($normalizedProduct);
    }

    private function denyAccessUnlessAclIsGranted(string $acl): void
    {
        if (!$this->security->isGranted($acl)) {
            throw new AccessDeniedHttpException($this->deniedAccessMessage($acl));
        }
    }

    private function deniedAccessMessage(string $acl): string
    {
        switch ($acl) {
            case 'pim_api_product_list':
                return 'Access forbidden. You are not allowed to list products.';
            case 'pim_api_product_edit':
                return 'Access forbidden. You are not allowed to create or update products.';
            case 'pim_api_product_remove':
                return 'Access forbidden. You are not allowed to delete products.';
            default:
                return 'Access forbidden.';
        }
    }
}
