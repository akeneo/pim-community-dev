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

use Akeneo\EnrichedEntity\Application\Record\EditRecord\EditRecordCommand;
use Akeneo\EnrichedEntity\Application\Record\EditRecord\EditRecordHandler;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Validate & save a record
 *
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2018 Akeneo SAS (https://www.akeneo.com)
 */
class EditAction
{
    /** @var EditRecordHandler */
    private $editRecordHandler;

    /** @var ValidatorInterface */
    private $validator;

    public function __construct(
        EditRecordHandler $editRecordHandler,
        ValidatorInterface $validator
    ) {
        $this->editRecordHandler = $editRecordHandler;
        $this->validator = $validator;
    }

    public function __invoke(Request $request): Response
    {
        if (!$request->isXmlHttpRequest()) {
            return new RedirectResponse('/');
        }
        if ($this->hasDesynchronizedIdentifiers($request)) {
            return new JsonResponse(
                'The identifier provided in the route and the one given in the body of the request are different',
                Response::HTTP_BAD_REQUEST
            );
        }

        $command = $this->getEditCommand($request);
        $violations = $this->validator->validate($command);

        if ($violations->count() > 0) {
            $errors = [];
            foreach ($violations as $violation) {
                $errors[$violation->getPropertyPath()] = [
                    'message' => $violation->getMessage()
                ];
            }

            return new JsonResponse($errors, Response::HTTP_BAD_REQUEST);
        }

        ($this->editRecordHandler)($command);

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * Checks whether the identifier given in the url parameter and in the body are the same or not.
     */
    private function hasDesynchronizedIdentifiers(Request $request): bool
    {
        $normalizedCommand = json_decode($request->getContent(), true);

        return $normalizedCommand['identifier']['enriched_entity_identifier'] !== $request->get('enrichedEntityIdentifier') ||
            $normalizedCommand['identifier']['identifier'] !== $request->get('recordIdentifier');
    }

    private function getEditCommand(Request $request): EditRecordCommand
    {
        $normalizedCommand = json_decode($request->getContent(), true);

        $command = new EditRecordCommand();
        $command->identifier = [
            'identifier'                 => $normalizedCommand['identifier']['identifier'] ?? null,
            'enriched_entity_identifier' => $normalizedCommand['identifier']['enriched_entity_identifier'] ?? null,
        ];
        $command->enrichedEntityIdentifier = $normalizedCommand['enriched_entity_identifier'] ?? null;
        $command->code = $normalizedCommand['code'] ?? null;
        $command->labels = $normalizedCommand['labels'] ?? [];

        return $command;
    }
}
