<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Controller\ExternalApi;

use Akeneo\Pim\Enrichment\Bundle\Event\TechnicalErrorEvent;
use Akeneo\Pim\Enrichment\Component\Error\DomainErrorInterface;
use Akeneo\Pim\Enrichment\Component\Product\Comparator\Filter\FilterInterface;
use Akeneo\Pim\Enrichment\Component\Product\Event\ProductDomainErrorEvent;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Validator\ExternalApi\PayloadFormat;
use Akeneo\Pim\Enrichment\Product\API\Command\Exception\LegacyViolationsException;
use Akeneo\Pim\Enrichment\Product\API\Command\Exception\UnknownAttributeException;
use Akeneo\Pim\Enrichment\Product\API\Command\Exception\UnknownUserIntentException;
use Akeneo\Pim\Enrichment\Product\API\Command\Exception\ViolationsException;
use Akeneo\Pim\Enrichment\Product\API\Command\UpsertProductCommand;
use Akeneo\Pim\Enrichment\Product\API\Query\GetUserIntentsFromStandardFormat;
use Akeneo\Pim\Structure\Component\Repository\ExternalApi\AttributeRepositoryInterface;
use Akeneo\Tool\Bundle\ApiBundle\Checker\DuplicateValueChecker;
use Akeneo\Tool\Bundle\ApiBundle\Documentation;
use Akeneo\Tool\Component\Api\Exception\DocumentedHttpException;
use Akeneo\Tool\Component\Api\Exception\ViolationHttpException;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyTypeException;
use Akeneo\Tool\Component\StorageUtils\Exception\PropertyException;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\UserManagement\Bundle\Context\UserContext;
use Doctrine\DBAL\Connection;
use Oro\Bundle\SecurityBundle\SecurityFacade;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\HandledStamp;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class UpdateProductByUuidController
{
    public function __construct(
        private AttributeRepositoryInterface $attributeRepository,
        private IdentifiableObjectRepositoryInterface $productRepository,
        private UrlGeneratorInterface $router,
        private FilterInterface $emptyValuesFilter,
        private EventDispatcherInterface $eventDispatcher,
        private DuplicateValueChecker $duplicateValueChecker,
        private SecurityFacade $security,
        private ValidatorInterface $validator,
        private UserContext $userContext,
        private MessageBusInterface $commandMessageBus,
        private MessageBusInterface $queryMessageBus,
        private Connection $connection
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

        try {
            $this->duplicateValueChecker->check($data);
        } catch (InvalidPropertyTypeException $exception) {
            $this->eventDispatcher->dispatch(new TechnicalErrorEvent($exception));
            $this->throwDocumentedHttpException($exception->getMessage(), $exception);
        }

        $product = $this->productRepository->find($uuid);
        $isCreation = null === $product;

        $this->validateUuidConsistency($uuid, $data);

        if (!$isCreation) {
            $data = $this->filterEmptyValues($product, $data);
        }

        $identifier = $this->getIdentifier($uuid, $data);

        try {
            $this->updateProduct($data, $identifier);
        } catch (UnknownUserIntentException $e) {
            $this->throwDocumentedHttpException(sprintf('Property "%s" does not exist.', $e->getFieldName()), $e);
        } catch (UnknownAttributeException $e) {
            $this->throwDocumentedHttpException(sprintf('The %s attribute does not exist in your PIM.', $e->getAttributeCode()), $e);
        } catch (\InvalidArgumentException $e) {
            $this->throwDocumentedHttpException($e->getMessage(), $e);
        } catch (ViolationsException $e) {
            $this->throwDocumentedHttpException($e->violations()->get(0)->getMessage(), $e);
        } catch (LegacyViolationsException $e) {
            $this->throwDocumentedHttpException($e->violations()->get(0)->getMessage(), $e);
        } catch (InvalidPropertyTypeException $e) {
            $this->throwDocumentedHttpException($e->getMessage(), $e);
        }

        $status = $isCreation ? Response::HTTP_CREATED : Response::HTTP_NO_CONTENT;
        $response = $this->getResponse(Uuid::fromString($uuid), $status);

        return $response;
    }

    private function getDecodedContent($content): array
    {
        $decodedContent = json_decode($content, true);

        if (null === $decodedContent) {
            throw new BadRequestHttpException('Invalid json message received');
        }

        return $decodedContent;
    }

    private function updateProduct(array $data, string $identifier): void
    {
        $envelope = $this->queryMessageBus->dispatch(new GetUserIntentsFromStandardFormat($data));
        $handledStamp = $envelope->last(HandledStamp::class);
        $userIntents = $handledStamp->getResult();

        $userId = $this->userContext->getUser()?->getId();
        $command = UpsertProductCommand::createFromCollection(
            $userId,
            $identifier,
            $userIntents
        );
        $this->commandMessageBus->dispatch($command);
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
        if (array_key_exists('uuid', $data) && $uuid !== $data['uuid']) {
            throw new UnprocessableEntityHttpException(
                sprintf(
                    'The uuid "%s" provided in the request body must match the uuid "%s" provided in the url.',
                    $data['uuid'],
                    $uuid
                )
            );
        }
    }

    private function throwDocumentedHttpException(string $message, \Exception $e)
    {
        throw new DocumentedHttpException(
            Documentation::URL . 'patch_products__code_',
            sprintf('%s Check the expected format on the API documentation.', $message),
            $e
        );
    }

    private function throwViolationException(string $message, string $propertyPath): void
    {
        $list = new ConstraintViolationList([
            new ConstraintViolation($message, $message, [], null, $propertyPath, null),
        ]);

        throw new ViolationHttpException($list);
    }

    private function getIdentifier(string $uuid, array $data): ?string
    {
        $identifier = $this->connection->fetchOne(
            'SELECT identifier FROM pim_catalog_product WHERE uuid = ?',
            [Uuid::fromString($uuid)->getBytes()]
        );

        if (false !== $identifier) {
            return $identifier;
        }

        if (isset($data['values'][$this->attributeRepository->getIdentifierCode()][0]['data'])) {
            return $data['values'][$this->attributeRepository->getIdentifierCode()][0]['data'];
        }

        return null;
    }
}
