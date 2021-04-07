<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\WorkOrganization\Workflow\Bundle\Widget;

use Akeneo\Pim\Permission\Component\Attributes;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\ProductDraft;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Repository\EntityWithValuesDraftRepositoryInterface;
use Akeneo\Platform\Bundle\DashboardBundle\Widget\WidgetInterface;
use Akeneo\Tool\Component\Localization\Presenter\PresenterInterface;
use Akeneo\UserManagement\Component\Model\UserInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Webmozart\Assert\Assert;

/**
 * Widget to display proposals
 *
 * @author Filips Alpe <filips@akeneo.com>
 */
class ProposalWidget implements WidgetInterface
{
    /** @var AuthorizationCheckerInterface */
    protected $authorizationChecker;

    /** @var EntityWithValuesDraftRepositoryInterface */
    protected $productDraftRepository;

    /** @var EntityWithValuesDraftRepositoryInterface */
    protected $productModelDraftRepository;

    /** @var TokenStorageInterface */
    protected $tokenStorage;

    /** @var PresenterInterface */
    protected $presenter;

    /** @var RouterInterface */
    protected $router;

    public function __construct(
        AuthorizationCheckerInterface $authorizationChecker,
        EntityWithValuesDraftRepositoryInterface $productDraftRepository,
        EntityWithValuesDraftRepositoryInterface $productModelDraftRepository,
        TokenStorageInterface $tokenStorage,
        PresenterInterface $presenter,
        RouterInterface $router
    ) {
        $this->authorizationChecker = $authorizationChecker;
        $this->productDraftRepository = $productDraftRepository;
        $this->productModelDraftRepository = $productModelDraftRepository;
        $this->tokenStorage = $tokenStorage;
        $this->presenter = $presenter;
        $this->router = $router;
    }

    /**
     * {@inheritdoc}
     */
    public function getAlias()
    {
        return 'proposals';
    }

    /**
     * {@inheritdoc}
     */
    public function getTemplate()
    {
        return 'AkeneoPimWorkflowBundle:Proposal/Widget:proposal.html.twig';
    }

    /**
     * {@inheritdoc}
     */
    public function getParameters()
    {
        return ['show' => $this->isDisplayable()];
    }

    /**
     * {@inheritdoc}
     */
    public function getData()
    {
        if (!$this->isDisplayable()) {
            return [];
        }

        $user = $this->tokenStorage->getToken()->getUser();
        Assert::implementsInterface($user, UserInterface::class);
        $result = [];
        $productProposals = $this->productDraftRepository->findApprovableByUser($user, 10);
        $productModelProposals = $this->productModelDraftRepository->findApprovableByUser($user, 10);

        $proposals = array_merge($productProposals, $productModelProposals);

        $locale = $user->getUiLocale()->getCode();

        $route = $this->router->generate('pimee_workflow_proposal_index');

        foreach ($proposals as $proposal) {
            $viewUrl = $this->router->generate(
                $proposal instanceof ProductDraft ? 'pim_enrich_product_edit': 'pim_enrich_product_model_edit',
                ['id' => $proposal->getEntityWithValue()->getId()]
            );

            $result[] = [
                'productId'        => $proposal->getEntityWithValue()->getId(),
                'productLabel'     => $proposal->getEntityWithValue()->getLabel(),
                'authorFullName'   => $proposal->getAuthorLabel(),
                'productViewUrl' => $viewUrl,
                'productReviewUrl' => $route . $this->getProposalGridParametersAsUrl(
                        $proposal->getAuthor(),
                        $proposal instanceof ProductDraft ? $proposal->getEntityWithValue()->getIdentifier() : $proposal->getEntityWithValue()->getCode()
                    ),
                'createdAt' => $this->presenter->present(
                    $proposal->getCreatedAt(),
                    [
                        'locale'   => $locale,
                        'timezone' => $user->getTimezone(),
                    ]
                )
            ];
        }

        return $result;
    }

    /**
     * Indicates if the widget should be displayed to the current user
     *
     * @return bool
     */
    protected function isDisplayable()
    {
        return $this->authorizationChecker->isGranted(Attributes::OWN_AT_LEAST_ONE_CATEGORY);
    }

    /**
     * @param string     $authorUsername
     * @param string|int $productIdentifier
     *
     * @return string
     */
    protected function getProposalGridParametersAsUrl($authorUsername, $productIdentifier)
    {
        $gridParameters = [
            'f' => [
                'author' => [
                    'value' => [
                        $authorUsername,
                    ],
                ],
                'identifier'    => [
                    'value' => $productIdentifier,
                    'type' => 1,
                ],
            ],
        ];

        return '|g/' . http_build_query($gridParameters, 'flags_');
    }
}
