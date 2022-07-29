<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Controller\ExternalApi;

use Akeneo\Pim\Enrichment\Bundle\Event\TechnicalErrorEvent;
use Akeneo\Pim\Enrichment\Component\Product\Validator\ExternalApi\PayloadFormat;
use Akeneo\Pim\Enrichment\Product\API\Command\Exception\ViolationsException;
use Akeneo\Pim\Enrichment\Product\API\Command\UpsertProductCommand;
use Akeneo\Pim\Enrichment\Product\API\Query\GetUserIntentsFromStandardFormat;
use Akeneo\Pim\Structure\Component\Repository\ExternalApi\AttributeRepositoryInterface;
use Akeneo\Tool\Bundle\ApiBundle\Checker\DuplicateValueChecker;
use Akeneo\Tool\Bundle\ApiBundle\Documentation;
use Akeneo\Tool\Component\Api\Exception\DocumentedHttpException;
use Akeneo\Tool\Component\Api\Exception\ViolationHttpException;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyTypeException;
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
class CreateProductByUuidController
{
    public function __construct(
        private AttributeRepositoryInterface $attributeRepository,
        private UrlGeneratorInterface $router,
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

        try {
            $this->duplicateValueChecker->check($data);
        } catch (InvalidPropertyTypeException $e) {
            $this->eventDispatcher->dispatch(new TechnicalErrorEvent($e));
            $this->throwDocumentedHttpException($e->getMessage(), $e);
        }

        $data = $this->orderData($data);
        $data = $this->replaceUuidsByIdentifiers($data);

        if (!isset($data['values'][$this->attributeRepository->getIdentifierCode()][0]['data'])) {
            $this->throwViolationException('The identifier attribute cannot be empty.', 'identifier');
        }

        if ($this->productAlreadyExists($data)) {
            $this->throwViolationException(
                sprintf('The %s identifier is already used for another product.', $this->getProductIdentifier($data)),
                'identifier'
            );
        }

        if (array_key_exists('variant_group', $data)) {
            throw new DocumentedHttpException(
                Documentation::URL_DOCUMENTATION . 'products-with-variants.html',
                'Property "variant_group" does not exist anymore. Check the link below to understand why.'
            );
        }

        try {
            $this->updateProduct($data);
        } catch (\InvalidArgumentException $e) {
            $message = $e->getMessage();
            $matches = [];
            if (preg_match('/^Cannot create userIntent from (?P<fieldName>.*) fieldName$/', $message, $matches)) {
                $this->throwDocumentedHttpException(sprintf('Property "%s" does not exist.', $matches['fieldName']), $e);
            }
            if (preg_match('/^Could not find the (?P<attributeCode>.*) attribute$/', $message, $matches)) {
                $this->throwDocumentedHttpException(sprintf('The %s attribute does not exist in your PIM.', $matches['attributeCode']), $e);
            }
            $this->throwDocumentedHttpException($message, $e);
        } catch (ViolationsException $e) {
            $constraintViolation = $e->violations()->get(0);
            $this->throwDocumentedHttpException($constraintViolation->getMessage(), $e);
        }

        return $this->getResponse($this->getUuidFromIdentifier($this->getProductIdentifier($data)), Response::HTTP_CREATED);
    }

    private function getDecodedContent($content): array
    {
        $decodedContent = json_decode($content, true);

        if (null === $decodedContent) {
            throw new BadRequestHttpException('Invalid json message received');
        }

        return $decodedContent;
    }

    private function updateProduct(array $data): void
    {
        $envelope = $this->queryMessageBus->dispatch(new GetUserIntentsFromStandardFormat($data));
        $handledStamp = $envelope->last(HandledStamp::class);
        $userIntents = $handledStamp->getResult();

        $userId = $this->userContext->getUser()?->getId();
        $command = UpsertProductCommand::createFromCollection(
            $userId,
            $this->getProductIdentifier($data),
            $userIntents
        );
        $this->commandMessageBus->dispatch($command);
    }

    private function getResponse(UuidInterface $uuid, int $status): Response
    {
        $response = new Response(null, $status);
        $route = $this->router->generate(
            'pim_api_product_uuid_get',
            ['uuid' => $uuid->toString()],
            UrlGeneratorInterface::ABSOLUTE_URL
        );
        $response->headers->set('Location', $route);

        return $response;
    }

    private function orderData(array $data): array
    {
        if (!isset($data['parent'])) {
            return $data;
        }

        return ['parent' => $data['parent']] + $data;
    }

    private function getProductIdentifier(array $data): string
    {
        return $data['values'][$this->attributeRepository->getIdentifierCode()][0]['data'];
    }

    private function getUuidFromIdentifier(string $productIdentifier): ?UuidInterface
    {
        $uuid = $this->connection->fetchOne(
            'SELECT BIN_TO_UUID(uuid) FROM pim_catalog_product WHERE identifier = ?', [$productIdentifier]
        );

        return false === $uuid ? null : Uuid::fromString($uuid);
    }

    /**
     * @param string[] $uuidAsStrings
     * @return string[]
     */
    private function getProductIdentifierFromUuids(array $uuidAsStrings): array
    {
        $uuidsAsBytes = array_map(fn (string $uuid): string => Uuid::fromString($uuid)->getBytes(), $uuidAsStrings);

        return $this->connection->fetchFirstColumn(
            'SELECT identifier FROM pim_catalog_product WHERE uuid IN(:uuids)',
            ['uuids' => $uuidsAsBytes],
            ['uuids' => Connection::PARAM_STR_ARRAY]
        );
    }

    private function throwDocumentedHttpException(string $message, \Exception $e)
    {
        throw new DocumentedHttpException(
            Documentation::URL . 'post_products',
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

    /**
     * This method is temporary until Public Service API manages UUIDS
     * @TODO CPM-697
     */
    private function replaceUuidsByIdentifiers(array $data)
    {
        if (!isset($data['associations'])) {
            return $data;
        }

        foreach ($data['associations'] as $associationCode => $associations) {
            if (isset($associations['products'])) {
                $data['associations'][$associationCode]['products'] = $this->getProductIdentifierFromUuids($data['associations'][$associationCode]['products']);
            }
        }

        return $data;
    }

    private function productAlreadyExists(array $data): bool
    {
        return null !== $this->getUuidFromIdentifier($this->getProductIdentifier($data));
    }
}
