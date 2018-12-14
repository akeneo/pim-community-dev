<?php

declare(strict_types=1);

namespace Akeneo\ReferenceEntity\Infrastructure\Controller\Attribute;

use Akeneo\ReferenceEntity\Application\Attribute\EditAttribute\CommandFactory\AbstractEditAttributeCommand;
use Akeneo\ReferenceEntity\Application\Attribute\EditAttribute\CommandFactory\EditAttributeCommandFactoryInterface;
use Akeneo\ReferenceEntity\Application\Attribute\EditAttribute\EditAttributeHandler;
use Akeneo\ReferenceEntity\Application\ReferenceEntity\Permission\CanEditReferenceEntityQuery;
use Akeneo\ReferenceEntity\Application\ReferenceEntity\Permission\CanEditReferenceEntityQueryHandler;
use Oro\Bundle\SecurityBundle\SecurityFacade;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class EditAction
{
    /** @var EditAttributeCommandFactoryInterface */
    private $editAttributeCommandFactory;

    /** @var EditAttributeHandler */
    private $editAttributeHandler;

    /** @var CanEditReferenceEntityQueryHandler */
    private $canEditReferenceEntityQueryHandler;

    /** @var TokenStorageInterface */
    private $tokenStorage;

    /** @var NormalizerInterface */
    private $normalizer;

    /** @var ValidatorInterface  */
    private $validator;

    /** @var SecurityFacade */
    private $securityFacade;

    public function __construct(
        EditAttributeCommandFactoryInterface $editAttributeCommandFactory,
        EditAttributeHandler $editAttributeHandler,
        CanEditReferenceEntityQueryHandler $canEditReferenceEntityQueryHandler,
        TokenStorageInterface $tokenStorage,
        NormalizerInterface $normalizer,
        ValidatorInterface $validator,
        SecurityFacade $securityFacade
    ) {
        $this->editAttributeCommandFactory = $editAttributeCommandFactory;
        $this->editAttributeHandler = $editAttributeHandler;
        $this->canEditReferenceEntityQueryHandler = $canEditReferenceEntityQueryHandler;
        $this->tokenStorage = $tokenStorage;
        $this->normalizer = $normalizer;
        $this->validator = $validator;
        $this->securityFacade = $securityFacade;
    }

    public function __invoke(Request $request)
    {
        if (!$request->isXmlHttpRequest()) {
            return new RedirectResponse('/');
        }
        if (!$this->isUserAllowedToEdit($request->get('referenceEntityIdentifier'))) {
            throw new AccessDeniedException();
        }
        if ($this->hasDesynchronizedIdentifier($request)) {
            return new JsonResponse(
                'The identifier provided in the route and the one given in the body of the request are different',
                Response::HTTP_BAD_REQUEST
            );
        }

        $command = $this->getEditCommand($request);
        $violations = $this->validator->validate($command);
        if ($violations->count() > 0) {
            return new JsonResponse(
                $this->normalizer->normalize($violations, 'internal_api'),
                Response::HTTP_BAD_REQUEST
            );
        }

        ($this->editAttributeHandler)($command);

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    private function isUserAllowedToEdit(string $referenceEntityCode): bool
    {
        $query = new CanEditReferenceEntityQuery();
        $query->principalIdentifier = $this->tokenStorage->getToken()->getUser()->getUsername();
        $query->referenceEntityIdentifier = $referenceEntityCode;

        return $this->securityFacade->isGranted('akeneo_referenceentity_attribute_edit')
            && ($this->canEditReferenceEntityQueryHandler)($query);
    }

    /**
     * Checks whether the identifier given in the url parameter and in the body are the same.
     */
    private function hasDesynchronizedIdentifier(Request $request): bool
    {
        $normalizedCommand = json_decode($request->getContent(), true);

        return $normalizedCommand['identifier'] !== $request->get('attributeIdentifier') ||
            $normalizedCommand['reference_entity_identifier'] !== $request->get('referenceEntityIdentifier');
    }

    private function getEditCommand(Request $request): AbstractEditAttributeCommand
    {
        $normalizedCommand = json_decode($request->getContent(), true);
        $command = $this->editAttributeCommandFactory->create($normalizedCommand);

        return $command;
    }
}
