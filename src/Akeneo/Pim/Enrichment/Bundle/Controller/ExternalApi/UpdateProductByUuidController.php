<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Controller\ExternalApi;

use Akeneo\Pim\Enrichment\Bundle\Event\ProductValidationErrorEvent;
use Akeneo\Pim\Enrichment\Bundle\Event\TechnicalErrorEvent;
use Akeneo\Pim\Enrichment\Component\Error\DomainErrorInterface;
use Akeneo\Pim\Enrichment\Component\Product\Builder\ProductBuilderInterface;
use Akeneo\Pim\Enrichment\Component\Product\Comparator\Filter\FilterInterface;
use Akeneo\Pim\Enrichment\Component\Product\EntityWithFamilyVariant\RemoveParentInterface;
use Akeneo\Pim\Enrichment\Component\Product\Event\ProductDomainErrorEvent;
use Akeneo\Pim\Enrichment\Component\Product\Exception\InvalidArgumentException as ProductInvalidArgumentException;
use Akeneo\Pim\Enrichment\Component\Product\Exception\TwoWayAssociationWithTheSameProductException;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\ProductModel\Filter\AttributeFilterInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\FindProduct;
use Akeneo\Pim\Enrichment\Component\Product\Validator\ExternalApi\PayloadFormat;
use Akeneo\Tool\Bundle\ApiBundle\Checker\DuplicateValueChecker;
use Akeneo\Tool\Bundle\ApiBundle\Documentation;
use Akeneo\Tool\Component\Api\Exception\DocumentedHttpException;
use Akeneo\Tool\Component\Api\Exception\ViolationHttpException;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyTypeException;
use Akeneo\Tool\Component\StorageUtils\Exception\PropertyException;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Oro\Bundle\SecurityBundle\SecurityFacade;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Exception\InvalidArgumentException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class UpdateProductByUuidController
{
    public function __construct(
        private FindProduct $findProduct,
        private UrlGeneratorInterface $router,
        private FilterInterface $emptyValuesFilter,
        private EventDispatcherInterface $eventDispatcher,
        private DuplicateValueChecker $duplicateValueChecker,
        private SecurityFacade $security,
        private ValidatorInterface $validator,
        private ObjectUpdaterInterface $updater,
        private ProductBuilderInterface $productBuilder,
        private SaverInterface $saver,
        private AttributeFilterInterface $productAttributeFilter,
        private ValidatorInterface $productValidator,
        private RemoveParentInterface $removeParent,
    ) {
    }

    public function __invoke(Request $request, string $uuid): Response
    {
        if (!$this->security->isGranted('pim_api_product_edit')) {
            throw new AccessDeniedHttpException('Access forbidden. You are not allowed to create or update products.');
        }

        $data = $this->getDecodedContent($request->getContent());
        $violations = $this->validator->validate($data, new PayloadFormat());

        if (0 < $violations->count()) {
            $firstViolation = $violations->get(0);
            $this->throwDocumentedHttpException($firstViolation->getMessage(), new \LogicException($firstViolation->getMessage()));
        }

        if (isset($data['identifier'])) {
            $this->throwDocumentedHttpException(
                'Property "identifier" does not exist.'
            );
        }

        try {
            $this->duplicateValueChecker->check($data);
        } catch (InvalidPropertyTypeException $exception) {
            $this->eventDispatcher->dispatch(new TechnicalErrorEvent($exception));
            $this->throwDocumentedHttpException($exception->getMessage(), $exception);
        }

        $product = $this->findProduct->withUuid($uuid);

        $isUpdate = true;
        if (null === $product) {
            $isUpdate = false;
            $product = $this->productBuilder->createProduct(uuid: $uuid);
        }

        $this->validateUuidConsistency($uuid, $data);
        $data['uuid'] = $uuid;

        if ($isUpdate) {
            $data = $this->filterEmptyValues($product, $data);
        }

        $data = $this->formatAssociatedProductUuids($data);

        $this->updateProduct($product, $data);
        $this->validateProduct($product);
        $this->saver->save($product);

        return $this->getResponse($product->getUuid(), $isUpdate ? Response::HTTP_NO_CONTENT : Response::HTTP_CREATED);
    }

    private function getDecodedContent($content): array
    {
        // TODO: CPM-718
        $decodedContent = json_decode($content, true);

        if (null === $decodedContent) {
            throw new BadRequestHttpException('Invalid json message received');
        }

        return $decodedContent;
    }

    private function updateProduct(ProductInterface $product, array $data): void
    {
        try {
            if ($this->needUpdateFromVariantToSimple($product, $data)) {
                $this->removeParent->from($product);
            }

            if (isset($data['parent']) || $product->isVariant()) {
                $data = $this->productAttributeFilter->filter($data);
            }

            $this->updater->update($product, $data);
        } catch (PropertyException $exception) {
            $this->eventDispatcher->dispatch(new TechnicalErrorEvent($exception));
            $this->throwDocumentedHttpException($exception->getMessage());
        } catch (TwoWayAssociationWithTheSameProductException $exception) {
            $this->eventDispatcher->dispatch(new TechnicalErrorEvent($exception));
            throw new DocumentedHttpException(
                TwoWayAssociationWithTheSameProductException::TWO_WAY_ASSOCIATIONS_HELP_URL,
                TwoWayAssociationWithTheSameProductException::TWO_WAY_ASSOCIATIONS_ERROR_MESSAGE,
                $exception
            );
        } catch (InvalidArgumentException | ProductInvalidArgumentException $exception) {
            $this->eventDispatcher->dispatch(new TechnicalErrorEvent($exception));
            throw new AccessDeniedHttpException($exception->getMessage(), $exception);
        } catch (DomainErrorInterface $exception) {
            $this->eventDispatcher->dispatch(new ProductDomainErrorEvent($exception, $product));

            throw $exception;
        } catch (\Exception $exception) {
            $this->eventDispatcher->dispatch(new TechnicalErrorEvent($exception));

            throw $exception;
        }
    }

    private function filterEmptyValues(ProductInterface $product, array $data): array
    {
        if (!isset($data['values'])) {
            return $data;
        }

        try {
            $dataFiltered = $this->emptyValuesFilter->filter($product, ['values' => $data['values']]);

            if (!empty($dataFiltered)) {
                $data = array_replace($data, $dataFiltered);
            } else {
                $data['values'] = [];
            }
        } catch (PropertyException $exception) {
            if ($exception instanceof DomainErrorInterface) {
                $this->eventDispatcher->dispatch(new ProductDomainErrorEvent($exception, $product));
            } else {
                $this->eventDispatcher->dispatch(new TechnicalErrorEvent($exception));
            }

            $this->throwDocumentedHttpException($exception->getMessage(), $exception);
        }

        return $data;
    }

    private function getResponse(UuidInterface $uuid, int $status): Response
    {
        $response = new Response(null, $status);
        $route = $this->router->generate(
            'pim_api_product_uuid_get',
            ['uuid' => $uuid],
            UrlGeneratorInterface::ABSOLUTE_URL
        );
        $response->headers->set('Location', $route);

        return $response;
    }

    private function validateUuidConsistency(string $uuid, array $data): void
    {
        if (isset($data['uuid']) && $uuid !== $data['uuid']) {
            throw new UnprocessableEntityHttpException(
                sprintf(
                    'The uuid "%s" provided in the request body must match the uuid "%s" provided in the url.',
                    $data['uuid'],
                    $uuid
                )
            );
        }
    }

    private function throwDocumentedHttpException(string $message, ?\Exception $previousException = null)
    {
        throw new DocumentedHttpException(
            Documentation::URL . 'patch_products_uuid__uuid_',
            sprintf('%s Check the expected format on the API documentation.', $message),
            $previousException
        );
    }

    /**
     * The API expects associations like:
     * {
     *     "XSELL": {
     *         "products": ["525365d0-8462-43e3-92dd-b02db13ba468", "2f68b3ff-6862-43c5-b4a8-78d0ed90cb75"],
     *     }
     * }
     *
     * But the standard format expects associations like:
     * {
     *     "XSELL": {
     *         "product_uuids": ["525365d0-8462-43e3-92dd-b02db13ba468", "2f68b3ff-6862-43c5-b4a8-78d0ed90cb75"],
     *     }
     * }
     *
     * This method only replace the key 'products' with 'products_uuid'.
     */
    private function formatAssociatedProductUuids(array $data): array
    {
        if (isset($data['associations'])) {
            foreach ($data['associations'] as $associationCode => $associations) {
                if (isset($associations['products'])) {
                    $data['associations'][$associationCode]['product_uuids'] = $associations['products'];
                    unset($data['associations'][$associationCode]['products']);
                }
            }
        }

        return $data;
    }

    private function validateProduct(ProductInterface $product): void
    {
        $violations = $this->productValidator->validate($product, null, ['Default', 'api']);
        if (0 !== $violations->count()) {
            $this->eventDispatcher->dispatch(new ProductValidationErrorEvent($violations, $product));

            throw new ViolationHttpException($violations);
        }
    }

    /**
     * It is a conversion from variant product to simple product if
     * - the product already exists
     * - it is a variant product
     * - and 'parent' is explicitly null
     */
    protected function needUpdateFromVariantToSimple(ProductInterface $product, array $data): bool
    {
        return null !== $product->getCreated() && $product->isVariant() &&
            array_key_exists('parent', $data) && null === $data['parent'];
    }
}
