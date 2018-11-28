<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\WorkOrganization\Workflow\Bundle\Controller;

use Akeneo\Channel\Component\Model\LocaleInterface;
use Akeneo\Channel\Component\Repository\ChannelRepositoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Manager\CompletenessManager;
use Akeneo\Pim\Permission\Bundle\User\UserContext;
use Akeneo\Pim\Permission\Component\Attributes;
use Akeneo\Pim\WorkOrganization\Workflow\Bundle\Manager\PublishedProductManager;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\PublishedProductInterface;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Published product controller
 *
 * @author Nicolas Dupont <nicolas@akeneo.com>
 */
class PublishedProductController
{
    /** @var RequestStack */
    protected $requestStack;

    /** @var RouterInterface */
    protected $router;

    /** @var EngineInterface */
    protected $templating;

    /** @var TranslatorInterface */
    protected $translator;

    /** @var UserContext */
    protected $userContext;

    /** @var PublishedProductManager */
    protected $manager;

    /** @var CompletenessManager */
    protected $completenessManager;

    /** @var ChannelRepositoryInterface */
    protected $channelRepository;

    /** @var AuthorizationCheckerInterface */
    protected $authorizationChecker;

    /**
     * @param RequestStack                  $requestStack
     * @param RouterInterface               $router
     * @param EngineInterface               $templating
     * @param TranslatorInterface           $translator
     * @param UserContext                   $userContext
     * @param PublishedProductManager       $manager
     * @param CompletenessManager           $completenessManager
     * @param ChannelRepositoryInterface    $channelRepository
     * @param AuthorizationCheckerInterface $authorizationChecker
     */
    public function __construct(
        RequestStack $requestStack,
        RouterInterface $router,
        EngineInterface $templating,
        TranslatorInterface $translator,
        UserContext $userContext,
        PublishedProductManager $manager,
        CompletenessManager $completenessManager,
        ChannelRepositoryInterface $channelRepository,
        AuthorizationCheckerInterface $authorizationChecker
    ) {
        $this->requestStack = $requestStack;
        $this->router = $router;
        $this->templating = $templating;
        $this->translator = $translator;
        $this->userContext = $userContext;
        $this->manager = $manager;
        $this->completenessManager = $completenessManager;
        $this->channelRepository = $channelRepository;
        $this->authorizationChecker = $authorizationChecker;
    }

    /**
     * Unpublish a product
     *
     * @param int|string $id
     *
     * @AclAncestor("pimee_workflow_published_product_index")
     *
     * @throws AccessDeniedException
     *
     * @return JsonResponse
     */
    public function unpublishAction($id)
    {
        $published = $this->findPublishedOr404($id);

        $isOwner = $this->authorizationChecker->isGranted(Attributes::OWN, $published->getOriginalProduct());
        if (!$isOwner) {
            throw new AccessDeniedException();
        }

        $this->manager->unpublish($published);

        return new JsonResponse(
            [
                'successful' => true,
                'message'    => $this->translator->trans('flash.product.unpublished')
            ]
        );
    }

    /**
     * View a published product
     *
     * @param int|string $id
     *
     * @AclAncestor("pimee_workflow_published_product_index")
     *
     * @return array
     */
    public function viewAction($id): Response
    {
        return $this->templating->renderResponse(
            'AkeneoPimWorkflowBundle:PublishedProduct:view.html.twig',
            ['productId' => $id]
        );
    }

    /**
     * Find a published product by its id or return a 404 response
     *
     * @param int|string $id
     *
     * @throws NotFoundHttpException
     *
     * @return PublishedProductInterface
     */
    protected function findPublishedOr404($id)
    {
        $published = $this->manager->findPublishedProductById($id);

        if (!$published) {
            throw new NotFoundHttpException(
                sprintf('Published product with id %s could not be found.', (string) $id)
            );
        }

        return $published;
    }

    /**
     * Return only granted user locales
     *
     * @return LocaleInterface[]
     */
    protected function getUserLocales(): array
    {
        return $this->userContext->getGrantedUserLocales();
    }

    /**
     * Get data locale code
     *
     * @return string
     */
    protected function getDataLocale()
    {
        return $this->userContext->getCurrentLocaleCode();
    }
}
