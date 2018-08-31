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

namespace Akeneo\EnrichedEntity\Infrastructure\Controller\Attribute;

use Akeneo\EnrichedEntity\Application\Attribute\CreateAttribute\AbstractCreateAttributeCommand;
use Akeneo\EnrichedEntity\Application\Attribute\CreateAttribute\CommandFactory\CreateAttributeCommandFactoryRegistryInterface;
use Akeneo\EnrichedEntity\Application\Attribute\CreateAttribute\CreateAttributeHandler;
use Akeneo\EnrichedEntity\Domain\Model\EnrichedEntity\EnrichedEntityIdentifier;
use Akeneo\EnrichedEntity\Domain\Query\Attribute\FindAttributeNextOrderInterface;
use Oro\Bundle\SecurityBundle\SecurityFacade;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class CreateAction
{
    /** @var CreateAttributeHandler */
    private $createAttributeHandler;

    /** @var FindAttributeNextOrderInterface */
    private $attributeNextOrder;

    /** @var SecurityFacade */
    private $securityFacade;

    /** @var NormalizerInterface */
    private $normalizer;

    /** @var ValidatorInterface */
    private $validator;

    /** @var CreateAttributeCommandFactoryRegistryInterface */
    private $attributeCommandFactoryRegistry;

    public function __construct(
        CreateAttributeHandler $createAttributeHandler,
        FindAttributeNextOrderInterface $attributeNextOrder,
        CreateAttributeCommandFactoryRegistryInterface $attributeCommandFactoryRegistry,
        NormalizerInterface $normalizer,
        ValidatorInterface $validator,
        SecurityFacade $securityFacade
    ) {
        $this->createAttributeHandler = $createAttributeHandler;
        $this->attributeNextOrder = $attributeNextOrder;
        $this->normalizer = $normalizer;
        $this->validator = $validator;
        $this->securityFacade = $securityFacade;
        $this->attributeCommandFactoryRegistry = $attributeCommandFactoryRegistry;
    }

    public function __invoke(Request $request, string $enrichedEntityIdentifier): Response
    {
        if (!$request->isXmlHttpRequest()) {
            return new RedirectResponse('/');
        }
        if (!$this->securityFacade->isGranted('akeneo_enrichedentity_attribute_create')) {
            throw new AccessDeniedException();
        }
        if (!$this->isAttributeTypeProvided($request)) {
            return new JsonResponse(
                'There was no valid attribute type provided in the request',
                Response::HTTP_BAD_REQUEST
            );
        }

        $command = $this->getCreateCommand($request);
        $violations = $this->validator->validate($command);

        if ($violations->count() > 0) {
            return new JsonResponse(
                $this->normalizer->normalize($violations, 'internal_api'),
                Response::HTTP_BAD_REQUEST
            );
        }

        ($this->createAttributeHandler)($command);

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    private function getCreateCommand(Request $request): AbstractCreateAttributeCommand
    {
        $normalizedCommand = json_decode($request->getContent(), true);

        $command = $this->attributeCommandFactoryRegistry->getFactory($normalizedCommand)->create($normalizedCommand);
        $command->order = $this->attributeNextOrder->withEnrichedEntityIdentifier(
            EnrichedEntityIdentifier::fromString($command->enrichedEntityIdentifier)
        );

        return $command;
    }

    private function isAttributeTypeProvided($request)
    {
        $content = json_decode($request->getContent(), true);

        return isset($content['type']) && is_string($content['type']);
    }
}
