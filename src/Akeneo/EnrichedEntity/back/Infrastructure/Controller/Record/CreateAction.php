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

namespace Akeneo\EnrichedEntity\Infrastructure\Controller\Record;

use Akeneo\EnrichedEntity\Application\Record\CreateRecord\CreateRecordCommand;
use Akeneo\EnrichedEntity\Application\Record\CreateRecord\CreateRecordHandler;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Validate & save a record
 *
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2018 Akeneo SAS (https://www.akeneo.com)
 */
class CreateAction
{
    /** @var CreateRecordHandler */
    private $createRecordHandler;

    /** @var ValidatorInterface */
    private $validator;

    /** @var NormalizerInterface */
    private $normalizer;

    public function __construct(
        CreateRecordHandler $createRecordHandler,
        NormalizerInterface $normalizer,
        ValidatorInterface $validator
    ) {
        $this->createRecordHandler = $createRecordHandler;
        $this->validator = $validator;
        $this->normalizer = $normalizer;
    }

    public function __invoke(Request $request, string $enrichedEntityIdentifier): Response
    {
        if (!$request->isXmlHttpRequest()) {
            return new RedirectResponse('/');
        }

        $command = $this->getCreateCommand($request);
        $violations = $this->validator->validate($command);

        if ($violations->count() > 0) {
            return new JsonResponse(
                $this->normalizer->normalize($violations, 'internal_api'),
                Response::HTTP_BAD_REQUEST
            );
        }

        if ($command->enrichedEntityIdentifier !== $enrichedEntityIdentifier) {
            return new JsonResponse(
                'Enriched Entity Identifier provided in the route and the one given in the body of your request are different',
                Response::HTTP_BAD_REQUEST
            );
        }

        ($this->createRecordHandler)($command);

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    private function getCreateCommand(Request $request): CreateRecordCommand
    {
        $normalizedCommand = json_decode($request->getContent(), true);

        $command = new CreateRecordCommand();
        $command->identifier = $normalizedCommand['identifier'] ?? null;
        $command->enrichedEntityIdentifier = $normalizedCommand['enrichedEntityIdentifier'] ?? null;
        $command->labels = $normalizedCommand['labels'] ?? [];

        return $command;
    }

    private function validateCommand(CreateRecordCommand $command): array
    {
        $errors = [];
        $violations = $this->validator->validate($command);

        if ($violations->count() > 0) {
            foreach ($violations as $violation) {
                $errors[$violation->getPropertyPath()] = $this->normalizer->normalize($violation, 'internal_api');
            }
        }

        return $errors;
    }
}
