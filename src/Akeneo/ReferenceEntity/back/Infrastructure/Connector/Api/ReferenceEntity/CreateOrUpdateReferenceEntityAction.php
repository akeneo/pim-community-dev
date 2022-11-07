<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\ReferenceEntity\Infrastructure\Connector\Api\ReferenceEntity;

use Akeneo\ReferenceEntity\Application\ReferenceEntity\CreateReferenceEntity\CreateReferenceEntityCommand;
use Akeneo\ReferenceEntity\Application\ReferenceEntity\CreateReferenceEntity\CreateReferenceEntityHandler;
use Akeneo\ReferenceEntity\Application\ReferenceEntity\EditReferenceEntity\EditReferenceEntityCommand;
use Akeneo\ReferenceEntity\Application\ReferenceEntity\EditReferenceEntity\EditReferenceEntityHandler;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Query\File\FindFileDataByFileKeyInterface;
use Akeneo\ReferenceEntity\Domain\Query\ReferenceEntity\ReferenceEntityExistsInterface;
use Akeneo\ReferenceEntity\Domain\Repository\ReferenceEntityRepositoryInterface;
use Akeneo\ReferenceEntity\Infrastructure\Connector\Api\JsonSchemaErrorsFormatter;
use Akeneo\ReferenceEntity\Infrastructure\Connector\Api\ReferenceEntity\JsonSchema\ReferenceEntityValidator;
use Akeneo\Tool\Component\Api\Exception\ViolationHttpException;
use Oro\Bundle\SecurityBundle\SecurityFacade;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\Router;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class CreateOrUpdateReferenceEntityAction
{
    public function __construct(
        private ReferenceEntityExistsInterface $referenceEntityExists,
        private ValidatorInterface $validator,
        private CreateReferenceEntityHandler $createReferenceEntityHandler,
        private EditReferenceEntityHandler $editReferenceEntityHandler,
        private Router $router,
        private ReferenceEntityValidator $jsonSchemaValidator,
        private FindFileDataByFileKeyInterface $findFileData,
        private ReferenceEntityRepositoryInterface $referenceEntityRepository,
        private SecurityFacade $securityFacade
    ) {
    }

    public function __invoke(Request $request, string $referenceEntityIdentifier): Response
    {
        $this->denyAccessUnlessAclIsGranted();
        try {
            $referenceEntityIdentifier = ReferenceEntityIdentifier::fromString($referenceEntityIdentifier);
        } catch (\Exception $exception) {
            throw new UnprocessableEntityHttpException($exception->getMessage());
        }

        $normalizedReferenceEntity = $this->getNormalizedReferenceEntity($request->getContent());
        $invalidFormatErrors = $this->validateReferenceEntityFormat($referenceEntityIdentifier, $normalizedReferenceEntity);

        if (!empty($invalidFormatErrors)) {
            return new JsonResponse([
                'code' => Response::HTTP_UNPROCESSABLE_ENTITY,
                'message' => 'The reference entity has an invalid format.',
                'errors' => JsonSchemaErrorsFormatter::format($invalidFormatErrors),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $createReferenceEntityCommand = null;
        $shouldBeCreated = !$this->referenceEntityExists->withIdentifier($referenceEntityIdentifier);
        if ($shouldBeCreated) {
            $createReferenceEntityCommand = new CreateReferenceEntityCommand(
                $normalizedReferenceEntity['code'],
                $normalizedReferenceEntity['labels'] ?? []
            );

            $violations = $this->validator->validate($createReferenceEntityCommand);
            if ($violations->count() > 0) {
                throw new ViolationHttpException($violations, 'The reference entity has data that does not comply with the business rules.');
            }
        }

        $editReferenceEntityCommand = new EditReferenceEntityCommand(
            $normalizedReferenceEntity['code'],
            $normalizedReferenceEntity['labels'] ?? [],
            null
        );

        if (array_key_exists('image', $normalizedReferenceEntity)) {
            $editReferenceEntityCommand->image = null !== $normalizedReferenceEntity['image'] ? $this->getImageData($normalizedReferenceEntity['image']) : null;
        } elseif (!$shouldBeCreated) {
            $referenceEntity = $this->referenceEntityRepository->getByIdentifier($referenceEntityIdentifier);
            $editReferenceEntityCommand->image = $referenceEntity->getImage()->normalize();
        }

        $violations = $this->validator->validate($editReferenceEntityCommand);
        if ($violations->count() > 0) {
            throw new ViolationHttpException($violations, 'The reference entity has data that does not comply with the business rules.');
        }

        if ($shouldBeCreated) {
            ($this->createReferenceEntityHandler)($createReferenceEntityCommand);
        }

        ($this->editReferenceEntityHandler)($editReferenceEntityCommand);

        $headers = [
            'location' => $this->router->generate('akeneo_reference_entities_reference_entity_rest_connector_get', [
                'code' => (string) $referenceEntityIdentifier,
            ], UrlGeneratorInterface::ABSOLUTE_URL)
        ];

        $responseStatusCode = $shouldBeCreated ? Response::HTTP_CREATED : Response::HTTP_NO_CONTENT;

        return new Response('', $responseStatusCode, $headers);
    }

    private function getNormalizedReferenceEntity(string $content): array
    {
        $normalizedReferenceEntity = json_decode($content, true);
        if (null === $normalizedReferenceEntity) {
            throw new BadRequestHttpException('Invalid json message received');
        }

        return $normalizedReferenceEntity;
    }

    private function validateReferenceEntityFormat(
        ReferenceEntityIdentifier $referenceEntityIdentifier,
        array $normalizedReferenceEntity
    ): array {
        $invalidFormatErrors = $this->jsonSchemaValidator->validate($normalizedReferenceEntity);

        if (empty($invalidFormatErrors)) {
            $inBodyReferenceEntityIdentifier = $normalizedReferenceEntity['code'] ?? null;
            if ((string) $referenceEntityIdentifier !== $inBodyReferenceEntityIdentifier) {
                throw new UnprocessableEntityHttpException('The code of the reference entity provided in the URI must be the same as the one provided in the request body.');
            }
        }

        return $invalidFormatErrors;
    }

    private function getImageData(string $imageFileKey): array
    {
        $imageData = $this->findFileData->find($imageFileKey);

        if (null === $imageData) {
            throw new UnprocessableEntityHttpException(sprintf(
                'The image "%s" was not found',
                $imageFileKey
            ));
        }

        return [
            'filePath' => $imageData['filePath'],
            'originalFilename' => $imageData['originalFilename'],
        ];
    }

    private function denyAccessUnlessAclIsGranted(): void
    {
        if (!$this->securityFacade->isGranted('pim_api_reference_entity_edit')) {
            throw new AccessDeniedHttpException('Access forbidden. You are not allowed to create or update reference entities.');
        }
    }
}
