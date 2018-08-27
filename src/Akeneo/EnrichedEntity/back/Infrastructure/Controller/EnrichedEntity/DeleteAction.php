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

namespace Akeneo\EnrichedEntity\Infrastructure\Controller\EnrichedEntity;

use Akeneo\EnrichedEntity\Application\EnrichedEntity\DeleteEnrichedEntity\DeleteEnrichedEntityCommand;
use Akeneo\EnrichedEntity\Application\EnrichedEntity\DeleteEnrichedEntity\DeleteEnrichedEntityHandler;
use Akeneo\EnrichedEntity\Domain\Repository\EnrichedEntityNotFoundException;
use Oro\Bundle\SecurityBundle\SecurityFacade;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Delete an Enriched Entity
 *
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2018 Akeneo SAS (https://www.akeneo.com)
 */
class DeleteAction
{
    /** @var SecurityFacade */
    private $securityFacade;

    /** @var NormalizerInterface */
    private $normalizer;

    /** @var ValidatorInterface */
    private $validator;

    /** @var DeleteEnrichedEntityHandler */
    private $deleteEnrichedEntityHandler;

    public function __construct(
        SecurityFacade $securityFacade,
        NormalizerInterface $normalizer,
        ValidatorInterface $validator,
        DeleteEnrichedEntityHandler $deleteEnrichedEntityHandler
    ) {
        $this->securityFacade = $securityFacade;
        $this->normalizer = $normalizer;
        $this->validator = $validator;
        $this->deleteEnrichedEntityHandler = $deleteEnrichedEntityHandler;
    }

    public function __invoke(Request $request, string $identifier): Response
    {
        if (!$request->isXmlHttpRequest()) {
            return new RedirectResponse('/');
        }

        if (!$this->securityFacade->isGranted('akeneo_enrichedentity_enriched_entity_delete')) {
            throw new AccessDeniedException();
        }

        $command = $this->getDeleteCommand($identifier);
        $violations = $this->validator->validate($command);

        if ($violations->count() > 0) {
            return new JsonResponse(
                $this->normalizer->normalize($violations, 'internal_api'),
                Response::HTTP_BAD_REQUEST
            );
        }

        try {
            ($this->deleteEnrichedEntityHandler)($command);
        } catch (EnrichedEntityNotFoundException $e) {
            return new JsonResponse(null, Response::HTTP_NOT_FOUND);
        }

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    private function getDeleteCommand(string $identifier): DeleteEnrichedEntityCommand
    {
        $command = new DeleteEnrichedEntityCommand();
        $command->identifier = $identifier;

        return $command;
    }
}
