<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Controller\ExternalApi;

use Akeneo\Pim\Enrichment\Bundle\Event\ProductValidationErrorEvent;
use Akeneo\Pim\Enrichment\Bundle\Event\TechnicalErrorEvent;
use Akeneo\Pim\Enrichment\Component\Error\DomainErrorInterface;
use Akeneo\Pim\Enrichment\Component\Product\Builder\ProductBuilderInterface;
use Akeneo\Pim\Enrichment\Component\Product\Event\ProductDomainErrorEvent;
use Akeneo\Pim\Enrichment\Component\Product\Exception\InvalidArgumentException as ProductInvalidArgumentException;
use Akeneo\Pim\Enrichment\Component\Product\Exception\TwoWayAssociationWithTheSameProductException;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\ProductModel\Filter\AttributeFilterInterface;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductRepositoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints\QuantifiedAssociations;
use Akeneo\Pim\Enrichment\Component\Product\Validator\ExternalApi\PayloadFormat;
use Akeneo\Pim\Enrichment\Product\Domain\Query\GetProductUuids;
use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;
use Akeneo\Tool\Bundle\ApiBundle\Checker\DuplicateValueChecker;
use Akeneo\Tool\Bundle\ApiBundle\Documentation;
use Akeneo\Tool\Component\Api\Exception\DocumentedHttpException;
use Akeneo\Tool\Component\Api\Exception\ViolationHttpException;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyTypeException;
use Akeneo\Tool\Component\StorageUtils\Exception\PropertyException;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Doctrine\DBAL\Connection;
use Oro\Bundle\SecurityBundle\SecurityFacade;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Exception\InvalidArgumentException;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Webmozart\Assert\Assert;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CreateProductByUuidController
{
    public function __construct(
        private UrlGeneratorInterface $router,
        private EventDispatcherInterface $eventDispatcher,
        private DuplicateValueChecker $duplicateValueChecker,
        private SecurityFacade $security,
        private ValidatorInterface $validator,
        private Connection $connection,
        private ObjectUpdaterInterface $updater,
        private ProductBuilderInterface $productBuilder,
        private SaverInterface $saver,
        private AttributeFilterInterface $productAttributeFilter,
        private ProductRepositoryInterface $productRepository,
        private GetProductUuids $getProductUuids,
        private AttributeRepositoryInterface $attributeRepository,
    ) {
    }

    public function __invoke(Request $request): Response
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

        if (isset($data['uuid']) && $this->productExists($data['uuid'])) {
            $this->throwViolationException(
                sprintf('The %s uuid is already used for another product.', $data['uuid']),
                'uuid'
            );
        }

        try {
            $this->duplicateValueChecker->check($data);
        } catch (InvalidPropertyTypeException $e) {
            $this->eventDispatcher->dispatch(new TechnicalErrorEvent($e));
            $this->throwDocumentedHttpException($e->getMessage(), $e);
        }

        if (isset($data['parent'])) {
            $identifierCode = $this->attributeRepository->getIdentifierCode();
            $product = $this->productBuilder->createProduct(identifier: $data['values'][$identifierCode][0]['data'] ?? null, uuid: $data['uuid'] ?? null);
        } else {
            $product = $this->productBuilder->createProduct(identifier: null, uuid: $data['uuid'] ?? null);
        }

        $data = $this->replaceUuidsByIdentifiers($data);
        $this->updateProduct($product, $data);
        $this->validateProduct($product);
        $this->saver->save($product);

        return $this->getResponse($product->getUuid());
    }

    private function updateProduct(ProductInterface $product, array $data): void
    {
        try {
            if (isset($data['parent'])) {
                $data = $this->productAttributeFilter->filter($data);
            }

            $this->updater->update($product, $data);
        } catch (PropertyException $exception) {
            $this->eventDispatcher->dispatch(new TechnicalErrorEvent($exception));
            $message = $exception->getMessage();
            $matches = [];
            // TODO: CPM-715
            if (preg_match('/^Property "associations" expects a valid product identifier. The product does not exist, "(?P<identifier>.*)" given.$/', $message, $matches)) {
                $message = sprintf(
                    'Property "associations" expects a valid product uuid. The product does not exist, "%s" given.',
                    $this->getProductUuids->fromIdentifier($matches['identifier'])
                );
            }

            throw new DocumentedHttpException(
                Documentation::URL . 'post_products',
                sprintf('%s Check the expected format on the API documentation.', $message),
                $exception
            );
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

    private function getDecodedContent($content): array
    {
        $decodedContent = json_decode($content, true);

        if (null === $decodedContent) {
            throw new BadRequestHttpException('Invalid json message received');
        }

        return $decodedContent;
    }

    private function getResponse(UuidInterface $uuid): Response
    {
        $response = new Response(null, Response::HTTP_CREATED);
        $route = $this->router->generate(
            'pim_api_product_uuid_get',
            ['uuid' => $uuid->toString()],
            UrlGeneratorInterface::ABSOLUTE_URL
        );
        $response->headers->set('Location', $route);

        return $response;
    }

    private function productExists(string $uuid): bool
    {
        return null !== $this->productRepository->find($uuid);
    }

    /**
     * @param string[] $uuidAsStrings
     * @param string $association 'associations'|'quantified_associations'
     * @return array<string, string>
     */
    private function getProductIdentifierFromUuids(array $uuidAsStrings, string $association): array
    {
        foreach ($uuidAsStrings as $uuid) {
            if (!Uuid::isValid($uuid)) {
                $this->throwDocumentedHttpException(
                    sprintf(
                        'Property "%s" expects a valid product uuid, "%s" given.',
                        $association,
                        $uuid
                    )
                );
            }
        }
        $uuidsAsBytes = array_map(fn (string $uuid): string => Uuid::fromString($uuid)->getBytes(), $uuidAsStrings);

        $result = $this->connection->fetchAllKeyValue(
            'SELECT BIN_TO_UUID(uuid) AS uuid, identifier FROM pim_catalog_product WHERE uuid IN(:uuids)',
            ['uuids' => $uuidsAsBytes],
            ['uuids' => Connection::PARAM_STR_ARRAY]
        );

        $diff = \array_diff($uuidAsStrings, \array_keys($result));
        if (count($diff) > 0) {
            $this->throwDocumentedHttpException(
                sprintf(
                    'Property "%s" expects a valid product uuid. The product does not exist, "%s" given.',
                    $association,
                    $diff[0]
                )
            );
        }

        return $result;
    }

    private function throwDocumentedHttpException(string $message, \Exception $previousException = null)
    {
        // TODO: CPM-711
        throw new DocumentedHttpException(
            Documentation::URL . 'post_products',
            sprintf('%s Check the expected format on the API documentation.', $message),
            $previousException
        );
    }

    private function throwViolationException(string $message, string $propertyPath): void
    {
        $list = new ConstraintViolationList([
            new ConstraintViolation($message, $message, [], null, $propertyPath, null),
        ]);

        throw new ViolationHttpException($list);
    }

    /**
     * This method is temporary until AssociationFieldSetter manages UUIDS
     * @TODO CPM-697
     * @param array $data
     * @return array
     * Expected data output format :
     * {   "associations": {
     *         "XSELL": {
     *             "groups": ["group1", "group2"],
     *             "products": ["AKN_TS1", "AKN_TSH2"],
     *             "product_models": ["MODEL_AKN_TS1", "MODEL_AKN_TSH2"]
     *         }
     *     },
     *     "quantified_associations": {
     *         "QUANTIFIED": {
     *             "products": [{"identifier": "AKN_TS1", quantity: 1}, {"identifier": "AKN_TSH2", "quantity": 2}],
     *             "product_models": ["MODEL_AKN_TS1", "MODEL_AKN_TSH2"]
     *         }
     *     }
     * }
     */
    private function replaceUuidsByIdentifiers(array $data): array
    {
        if (isset($data['associations'])) {
            foreach ($data['associations'] as $associationCode => $associations) {
                if (isset($associations['products'])) {
                    $data['associations'][$associationCode]['products'] = \array_values($this->getProductIdentifierFromUuids($associations['products'], 'associations'));
                }
            }
        }

        if (isset($data['quantified_associations'])) {
            foreach ($data['quantified_associations'] as $associationCode => $associations) {
                if (isset($associations['products'])) {
                    $map = $this->getProductIdentifierFromUuids(
                        \array_map(fn (array $association): string => $association['uuid'], $associations['products']),
                        'quantified_associations'
                    );
                    $data['quantified_associations'][$associationCode]['products'] = \array_map(function ($association) use ($map) {
                        return ['quantity' => $association['quantity'], 'identifier' => $map[$association['uuid']]];
                    }, $associations['products']);
                }
            }
        }

        return $data;
    }

    private function validateProduct(ProductInterface $product): void
    {
        $violations = $this->validator->validate($product, null, ['Default', 'api']);
        if (0 !== $violations->count()) {
            foreach ($violations as $offset => $violation) {
                Assert::isInstanceOf($violation, ConstraintViolation::class);
                /** @var ConstraintViolation $violation */
                if (QuantifiedAssociations::PRODUCTS_DO_NOT_EXIST_ERROR === $violation->getCode()) {
                    $parameters = $violation->getParameters();
                    $uuid = $this->getProductUuids->fromIdentifier($parameters['{{ values }}']);
                    $parameters = ['{{ values }}' => $uuid->toString()];
                    $message = sprintf('The following products don\'t exist: %s. Please make sure the products haven\'t been deleted in the meantime.', $uuid->toString());

                    $violations->set($offset, new ConstraintViolation(
                        $message,
                        $violation->getMessageTemplate(),
                        $parameters,
                        $violation->getRoot(),
                        $violation->getPropertyPath(),
                        $violation->getInvalidValue(),
                        $violation->getPlural(),
                        $violation->getCode(),
                        $violation->getConstraint(),
                        $violation->getCause()
                    ));
                }
            }
            $this->eventDispatcher->dispatch(new ProductValidationErrorEvent($violations, $product));

            throw new ViolationHttpException($violations);
        }
    }
}
