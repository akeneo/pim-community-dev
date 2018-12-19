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
use Akeneo\ReferenceEntity\Domain\Query\ReferenceEntity\ReferenceEntityExistsInterface;
use Akeneo\Tool\Component\Api\Exception\ViolationHttpException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\Router;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class CreateOrUpdateReferenceEntityAction
{
    /** @var ReferenceEntityExistsInterface */
    private $referenceEntityExists;

    /** @var ValidatorInterface */
    private $validator;

    /** @var CreateReferenceEntityHandler */
    private $createReferenceEntityHandler;

    /** @var EditReferenceEntityHandler */
    private $editReferenceEntityHandler;

    /** @var Router */
    private $router;

    public function __construct(
        ReferenceEntityExistsInterface $referenceEntityExists,
        ValidatorInterface $validator,
        CreateReferenceEntityHandler $createReferenceEntityHandler,
        EditReferenceEntityHandler $editReferenceEntityHandler,
        Router $router
    ) {
        $this->referenceEntityExists = $referenceEntityExists;
        $this->validator = $validator;
        $this->createReferenceEntityHandler = $createReferenceEntityHandler;
        $this->editReferenceEntityHandler = $editReferenceEntityHandler;
        $this->router = $router;
    }

    public function __invoke(Request $request, string $referenceEntityIdentifier): Response
    {
        $normalizedReferenceEntity = json_decode($request->getContent(), true);
        $referenceEntityIdentifierInBody = $normalizedReferenceEntity['code'] ?? null;
        if ($referenceEntityIdentifier !== $referenceEntityIdentifierInBody) {
            throw new UnprocessableEntityHttpException('The code of the record provided in the URI must be the same as the one provided in the request body.');
        }

        try {
            $referenceEntityIdentifier = ReferenceEntityIdentifier::fromString($referenceEntityIdentifier);
        } catch (\Exception $exception) {
            throw new UnprocessableEntityHttpException($exception->getMessage());
        }

        $createReferenceEntityCommand = null;
        $shouldBeCreated = $this->referenceEntityExists->withIdentifier($referenceEntityIdentifier);
        if (true === $shouldBeCreated) {
            $createReferenceEntityCommand = new CreateReferenceEntityCommand();
            $createReferenceEntityCommand->code = $referenceEntityIdentifier;
            $createReferenceEntityCommand->labels = $normalizedReferenceEntity['labels'];

            $violations = $this->validator->validate($createReferenceEntityCommand);
            if ($violations->count() > 0) {
                throw new ViolationHttpException($violations, 'The record has data that does not comply with the business rules.');
            }
        }

        $editReferenceEntityCommand = new EditReferenceEntityCommand();
        $editReferenceEntityCommand->identifier = $referenceEntityIdentifier;
        $editReferenceEntityCommand->labels = $normalizedReferenceEntity['labels'];
        // TODO: to fix as the command does not accept a code
        $editReferenceEntityCommand->image = null;

        $violations = $this->validator->validate($editReferenceEntityCommand);
        if ($violations->count() > 0) {
            throw new ViolationHttpException($violations, 'The record has data that does not comply with the business rules.');
        }

        if (true === $shouldBeCreated) {
            ($this->createReferenceEntityHandler)($createReferenceEntityCommand);
        }

        ($this->editReferenceEntityHandler)($editReferenceEntityCommand);

        $headers = [
            'location' => $this->router->generate('akeneo_reference_entities_reference_entity_rest_connector_get', [
                'referenceEntityIdentifier' => (string) $referenceEntityIdentifier,
            ], UrlGeneratorInterface::ABSOLUTE_URL)
        ];

        $responseStatusCode = true === $shouldBeCreated ? Response::HTTP_CREATED : Response::HTTP_NO_CONTENT;

        return Response::create('', $responseStatusCode, $headers);
    }
}
