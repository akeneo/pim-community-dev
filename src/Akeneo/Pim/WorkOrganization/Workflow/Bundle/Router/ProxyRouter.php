<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2017 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\WorkOrganization\Workflow\Bundle\Router;

use Akeneo\Pim\WorkOrganization\Workflow\Component\Repository\EntityWithValuesDraftRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Exception\MissingMandatoryParametersException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * When a product is saved, we send the URI of the product in the headers.
 * This proxy checks if a draft exists for a user and redirect him to product draft route instead of product route.
 *
 * @author Marie Bochu <marie.bochu@akeneo.com>
 */
class ProxyRouter implements UrlGeneratorInterface
{
    /** @var UrlGeneratorInterface */
    private $router;

    /** @var TokenStorageInterface */
    private $tokenStorage;

    /** @var EntityWithValuesDraftRepositoryInterface */
    private $draftRepository;

    /** @var IdentifiableObjectRepositoryInterface */
    private $identifiableObjectRepository;

    /** @var string */
    private $newRoute;

    public function __construct(
        UrlGeneratorInterface $router,
        TokenStorageInterface $tokenStorage,
        EntityWithValuesDraftRepositoryInterface $draftRepository,
        IdentifiableObjectRepositoryInterface $identifiableObjectRepository,
        string $newRoute
    ) {
        $this->router = $router;
        $this->tokenStorage = $tokenStorage;
        $this->draftRepository = $draftRepository;
        $this->identifiableObjectRepository = $identifiableObjectRepository;
        $this->newRoute = $newRoute;
    }

    /**
     * {@inheritdoc}
     */
    public function generate($name, $parameters = [], $referenceType = UrlGeneratorInterface::ABSOLUTE_PATH)
    {
        if (!isset($parameters['code'])) {
            throw new MissingMandatoryParametersException('Parameter "code" is missing in the parameters');
        }

        $entity = $this->identifiableObjectRepository->findOneByIdentifier($parameters['code']);
        if (null === $entity) {
            throw new NotFoundHttpException(sprintf('Entity "%s" does not exist', $parameters['code']));
        }

        $username = $this->tokenStorage->getToken()->getUser()->getUsername();
        $name = null === $this->draftRepository->findUserEntityWithValuesDraft($entity, $username)
            ? $name : $this->newRoute;

        return $this->router->generate($name, $parameters, $referenceType);
    }

    /**
     * {@inheritdoc}
     */
    public function setContext(RequestContext $context)
    {
        $this->router->setContext($context);
    }

    /**
     * {@inheritdoc}
     */
    public function getContext()
    {
        $this->router->getContext();
    }
}
