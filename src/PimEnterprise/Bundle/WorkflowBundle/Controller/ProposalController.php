<?php

namespace PimEnterprise\Bundle\WorkflowBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Validator\ValidatorInterface;
use Doctrine\Common\Persistence\ObjectRepository;
use Pim\Bundle\EnrichBundle\AbstractController\AbstractController;
use PimEnterprise\Bundle\WorkflowBundle\Manager\ProposalManager;

/**
 * Proposal controller
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class ProposalController extends AbstractController
{
    /** @var ObjectRepository */
    protected $proposalRepository;

    /** @var ProposalManager */
    protected $proposalManager;

    /**
     * @param Request                  $request
     * @param EngineInterface          $templating
     * @param RouterInterface          $router
     * @param SecurityContextInterface $securityContext
     * @param FormFactoryInterface     $formFactory
     * @param ValidatorInterface       $validator
     * @param TranslatorInterface      $translator
     * @param ObjectRepository         $proposalRepository
     * @param ProposalManager          $proposalManager
     */
    public function __construct(
        Request $request,
        EngineInterface $templating,
        RouterInterface $router,
        SecurityContextInterface $securityContext,
        FormFactoryInterface $formFactory,
        ValidatorInterface $validator,
        TranslatorInterface $translator,
        ObjectRepository $proposalRepository,
        ProposalManager $proposalManager
    ) {
        parent::__construct(
            $request,
            $templating,
            $router,
            $securityContext,
            $formFactory,
            $validator,
            $translator
        );
        $this->proposalRepository = $proposalRepository;
        $this->proposalManager = $proposalManager;
    }

    /**
     * @param integer|string $id
     *
     * @return RedirectResponse
     * @throws NotFoundHttpException
     */
    public function approveAction($id)
    {
        if (null === $proposal = $this->proposalRepository->findOpen($id)) {
            throw new NotFoundHttpException(sprintf('Proposal "%s" not found', $id));
        }

        $this->proposalManager->approve($proposal);

        return $this->redirect(
            $this->generateUrl(
                'pim_enrich_product_edit',
                [
                    'id' => $proposal->getProduct()->getId()
                ]
            )
        );
    }
}
