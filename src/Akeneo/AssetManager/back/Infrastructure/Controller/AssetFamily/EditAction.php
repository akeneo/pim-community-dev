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

namespace Akeneo\AssetManager\Infrastructure\Controller\AssetFamily;

use Akeneo\AssetManager\Application\AssetFamily\EditAssetFamily\EditAssetFamilyCommand;
use Akeneo\AssetManager\Application\AssetFamily\EditAssetFamily\EditAssetFamilyHandler;
use Akeneo\AssetManager\Application\AssetFamilyPermission\CanEditAssetFamily\CanEditAssetFamilyQuery;
use Akeneo\AssetManager\Application\AssetFamilyPermission\CanEditAssetFamily\CanEditAssetFamilyQueryHandler;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\AssetManager\Domain\Repository\AssetFamilyRepositoryInterface;
use Akeneo\AssetManager\Domain\Repository\AttributeNotFoundException;
use Akeneo\AssetManager\Domain\Repository\AttributeRepositoryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Validate & save an asset family
 *
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2018 Akeneo SAS (https://www.akeneo.com)
 */
class EditAction
{
    /** @var EditAssetFamilyHandler */
    private $editAssetFamilyHandler;

    /** @var Serializer */
    private $serializer;

    /** @var ValidatorInterface */
    private $validator;

    /** @var CanEditAssetFamilyQueryHandler */
    private $canEditAssetFamilyQueryHandler;

    /** @var TokenStorageInterface */
    private $tokenStorage;

    /** @var AttributeRepositoryInterface */
    private $attributeRepository;

    /** @var AssetFamilyRepositoryInterface */
    private $assetFamilyRepository;

    public function __construct(
        EditAssetFamilyHandler $editAssetFamilyHandler,
        CanEditAssetFamilyQueryHandler $canEditAssetFamilyQueryHandler,
        TokenStorageInterface $tokenStorage,
        Serializer $serializer,
        ValidatorInterface $validator,
        AttributeRepositoryInterface $attributeRepository,
        AssetFamilyRepositoryInterface $assetFamilyRepository
    ) {
        $this->editAssetFamilyHandler = $editAssetFamilyHandler;
        $this->canEditAssetFamilyQueryHandler = $canEditAssetFamilyQueryHandler;
        $this->tokenStorage = $tokenStorage;
        $this->serializer = $serializer;
        $this->validator = $validator;
        $this->attributeRepository = $attributeRepository;
        $this->assetFamilyRepository = $assetFamilyRepository;
    }

    public function __invoke(Request $request): Response
    {
        if (!$request->isXmlHttpRequest()) {
            return new RedirectResponse('/');
        }
        if ($this->hasDesynchronizedIdentifier($request)) {
            return new JsonResponse(
                'Asset family identifier provided in the route and the one given in the body of your request are different',
                Response::HTTP_BAD_REQUEST
            );
        }
        if (!$this->isUserAllowedToEdit($request->get('identifier'))) {
            throw new AccessDeniedHttpException();
        }

        $parameters = json_decode($request->getContent(), true);
        $parameters = $this->replaceAttributeAsMainMediaIdentifierByCode($parameters);

        $command = new EditAssetFamilyCommand(
            $parameters['identifier'],
            $parameters['labels'],
            $parameters['image'],
            $parameters['attributeAsMainMedia'],
            $parameters['productLinkRules'],
            $parameters['transformations']
        );

        $violations = $this->validator->validate($command);

        if ($violations->count() > 0) {
            return new JsonResponse($this->serializer->normalize($violations, 'internal_api'),
                Response::HTTP_BAD_REQUEST);
        }

        ($this->editAssetFamilyHandler)($command);

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * Checks whether the identifier given in the url parameter and in the body are the same or not.
     */
    private function hasDesynchronizedIdentifier(Request $request): bool
    {
        $normalizedCommand = json_decode($request->getContent(), true);

        return $normalizedCommand['identifier'] !== $request->get('identifier');
    }

    private function isUserAllowedToEdit(string $assetFamilyIdentifier): bool
    {
        $query = new CanEditAssetFamilyQuery(
            $assetFamilyIdentifier,
            $this->tokenStorage->getToken()->getUser()->getUsername()
        );
        $isAllowedToEdit = ($this->canEditAssetFamilyQueryHandler)($query);

        return $isAllowedToEdit; // && add Check of ACLs
    }

    /**
     * The frontend gives us the Identifier of the attribute as main media,
     * but the EditAssetFamilyCommand requires the Code of the attribute,
     * this is why we retrieve the code here and updates the parameters of the request.
     */
    private function replaceAttributeAsMainMediaIdentifierByCode(array $parameters)
    {
        $attributeAsMainMediaIdentifier = $parameters['attributeAsMainMedia'];

        try {
            $attribute = $this->attributeRepository->getByIdentifier(
                AttributeIdentifier::fromString($attributeAsMainMediaIdentifier)
            );

            $attributeAsMainMediaCode = (string) $attribute->getCode();
        } catch (AttributeNotFoundException $e) {
            $attributeAsMainMediaCode = null;
        }

        return array_merge($parameters, ['attributeAsMainMedia' => $attributeAsMainMediaCode]);
    }
}
