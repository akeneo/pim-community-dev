<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Infrastructure\Controller\Internal;

use Akeneo\Catalogs\Application\Exception\CatalogNotFoundException;
use Akeneo\Catalogs\Application\Persistence\Attribute\SearchAttributesQueryInterface;
use Akeneo\Catalogs\Application\Persistence\Catalog\GetCatalogQueryInterface;
use Akeneo\Catalogs\Application\Persistence\ProductMappingSchema\ExistsProductMappingSchemaQueryInterface;
use Akeneo\Catalogs\ServiceAPI\Query\GetProductMappingSchemaQuery;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class GetAutoFilledMappingWithAI
{
    public function __construct(
        readonly private GetCatalogQueryInterface $getCatalogQuery,
        readonly private ExistsProductMappingSchemaQueryInterface $existsProductMappingSchemaQuery,
        private SearchAttributesQueryInterface $searchAttributesQuery,
    ) {
    }

    public function __invoke(Request $request, string $catalogId): Response
    {
        if (!$request->isXmlHttpRequest()) {
            return new RedirectResponse('/');
        }

        $scope = $request->query->get('scope', 'ecommerce');
        $locale = (int) $request->query->get('locale', 'en_US');

        try {
            // on a notre mapping
            $catalog = $this->getCatalogQuery->execute($catalogId);

            //+ on veut les targets de notre schema
            $productMappingSchema = $this->queryBus->execute(new GetProductMappingSchemaQuery($catalogId));

            $targets = array_keys($productMappingSchema['properties']);

            dump($targets);

            //+ on récupère les attributs du pim
            $attributes = $this->searchAttributesQuery->execute();

            dd($attributes);


            //+ on fait un appel openai



        } catch (CatalogNotFoundException) {
            throw new NotFoundHttpException(\sprintf('catalog "%s" does not exist.', $catalogId));
        }

        return new JsonResponse([
            ...$catalog->normalize(),
            'has_product_mapping_schema' => $this->existsProductMappingSchemaQuery->execute($catalogId),
        ]);
    }
}
