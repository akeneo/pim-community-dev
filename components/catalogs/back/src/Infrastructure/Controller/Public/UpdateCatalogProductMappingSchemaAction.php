<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Infrastructure\Controller\Public;

use Akeneo\Catalogs\Infrastructure\Security\DenyAccessUnlessGrantedTrait;
use Akeneo\Catalogs\Infrastructure\Security\GetCurrentUsernameTrait;
use Akeneo\Catalogs\ServiceAPI\Command\UpdateCatalogProductMappingSchemaCommand;
use Akeneo\Catalogs\ServiceAPI\Messenger\CommandBus;
use Akeneo\Catalogs\ServiceAPI\Messenger\QueryBus;
use Akeneo\Catalogs\ServiceAPI\Model\Catalog;
use Akeneo\Catalogs\ServiceAPI\Query\GetCatalogProductMappingSchemaQuery;
use Akeneo\Catalogs\ServiceAPI\Query\GetCatalogQuery;
use Akeneo\Platform\Bundle\FrameworkBundle\Security\SecurityFacadeInterface;
use Akeneo\Tool\Component\Api\Exception\ViolationHttpException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Messenger\Exception\ValidationFailedException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class UpdateCatalogProductMappingSchemaAction
{
    use DenyAccessUnlessGrantedTrait;
    use GetCurrentUsernameTrait;

    public function __construct(
        private CommandBus $commandBus,
        private QueryBus $queryBus,
        private TokenStorageInterface $tokenStorage,
        private SecurityFacadeInterface $security,
    ) {
    }

    public function __invoke(Request $request, string $catalogId): Response
    {
        $this->denyAccessUnlessGrantedToEditCatalogs();

        $catalog = $this->getCatalog($catalogId);

        $this->denyAccessUnlessOwnerOfCatalog($catalog, $this->getCurrentUsername());

        try {
            /** @var object $productMappingSchemaPayload */
            $productMappingSchemaPayload = \json_decode((string) $request->getContent(), false, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException) {
            throw new BadRequestHttpException('Invalid json message received');
        }

        try {
            $this->commandBus->execute(new UpdateCatalogProductMappingSchemaCommand($catalogId, $productMappingSchemaPayload));
        } catch (ValidationFailedException $e) {
            throw new ViolationHttpException($e->getViolations());
        }

        $productMappingSchema = $this->queryBus->execute(new GetCatalogProductMappingSchemaQuery($catalogId));

        return new JsonResponse($productMappingSchema, Response::HTTP_OK);
    }

    private function getCatalog(string $id): Catalog
    {
        try {
            $catalog = $this->queryBus->execute(new GetCatalogQuery($id));
        } catch (ValidationFailedException $e) {
            throw new NotFoundHttpException(\sprintf('Catalog "%s" does not exist or you can\'t access it.', $id), $e);
        }

        if (null === $catalog) {
            throw new NotFoundHttpException(\sprintf('Catalog "%s" does not exist or you can\'t access it.', $id));
        }

        return $catalog;
    }
}
