<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Infrastructure\Controller\Internal;

use Akeneo\Catalogs\Application\Storage\CatalogsMappingStorageInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class GetProductMappingSchemaAction
{
    public function __construct(
        private CatalogsMappingStorageInterface $catalogsMappingStorage,
    ) {
    }

    public function __invoke(Request $request, string $catalogId): Response
    {
        if (!$request->isXmlHttpRequest()) {
            return new RedirectResponse('/');
        }

        return new JsonResponse($this->getProductMappingSchema($catalogId));
    }

    /**
     * @return array<string, mixed>
     */
    private function getProductMappingSchema(string $catalogId): array
    {
        $productMappingSchemaFile = \sprintf('%s_product.json', $catalogId);

        if (!$this->catalogsMappingStorage->exists($productMappingSchemaFile)) {
            throw new NotFoundHttpException();
        }

        $productMappingSchemaRaw = \stream_get_contents(
            $this->catalogsMappingStorage->read($productMappingSchemaFile),
        );

        if (false === $productMappingSchemaRaw) {
            throw new \LogicException('Product mapping schema is unreadable.');
        }

        /** @var array<string, mixed> $productMappingSchema */
        $productMappingSchema = \json_decode($productMappingSchemaRaw, true, 512, JSON_THROW_ON_ERROR);

        return $productMappingSchema;
    }
}
