<?php

namespace Pim\Bundle\EnrichBundle\Controller;

use Akeneo\Component\StorageUtils\Remover\RemoverInterface;
use Doctrine\Common\Persistence\ManagerRegistry;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Pim\Bundle\CatalogBundle\Entity\AssociationType;
use Pim\Bundle\CatalogBundle\Manager\AssociationManager;
use Pim\Bundle\CatalogBundle\Manager\AssociationTypeManager;
use Pim\Bundle\EnrichBundle\AbstractController\AbstractDoctrineController;
use Pim\Bundle\EnrichBundle\Form\Handler\HandlerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Association type controller
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AssociationTypeController extends AbstractDoctrineController
{
    /** @var HandlerInterface */
    protected $assocTypeHandler;

    /** @var Form */
    protected $assocTypeForm;

    /** @var AssociationTypeManager */
    protected $assocTypeManager;

    /** @var AssociationManager */
    protected $assocManager;

    /** @var RemoverInterface */
    protected $assocTypeRemover;

    /**
     * Constructor
     *
     * @param Request                  $request
     * @param EngineInterface          $templating
     * @param RouterInterface          $router
     * @param TokenStorageInterface    $tokenStorage
     * @param FormFactoryInterface     $formFactory
     * @param ValidatorInterface       $validator
     * @param TranslatorInterface      $translator
     * @param EventDispatcherInterface $eventDispatcher
     * @param ManagerRegistry          $doctrine
     * @param AssociationTypeManager   $assocTypeManager
     * @param AssociationManager       $assocManager
     * @param HandlerInterface         $assocTypeHandler
     * @param Form                     $assocTypeForm
     * @param RemoverInterface         $assocTypeRemover
     */
    public function __construct(
        Request $request,
        EngineInterface $templating,
        RouterInterface $router,
        TokenStorageInterface $tokenStorage,
        FormFactoryInterface $formFactory,
        ValidatorInterface $validator,
        TranslatorInterface $translator,
        EventDispatcherInterface $eventDispatcher,
        ManagerRegistry $doctrine,
        AssociationTypeManager $assocTypeManager,
        AssociationManager $assocManager,
        HandlerInterface $assocTypeHandler,
        Form $assocTypeForm,
        RemoverInterface $assocTypeRemover
    ) {
        parent::__construct(
            $request,
            $templating,
            $router,
            $tokenStorage,
            $formFactory,
            $validator,
            $translator,
            $eventDispatcher,
            $doctrine
        );

        $this->assocTypeManager = $assocTypeManager;
        $this->assocManager     = $assocManager;
        $this->assocTypeHandler = $assocTypeHandler;
        $this->assocTypeForm    = $assocTypeForm;
        $this->assocTypeRemover = $assocTypeRemover;
    }

    /**
     * List association types
     *
     * @param Request $request
     *
     * @Template
     * @AclAncestor("pim_enrich_associationtype_index")
     *
     * @return Response
     */
    public function indexAction(Request $request)
    {
        return [];
    }

    /**
     * Create an association type
     *
     * @param Request $request
     *
     * @Template
     * @AclAncestor("pim_enrich_associationtype_create")
     *
     * @return Response|RedirectResponse
     */
    public function createAction(Request $request)
    {
        if (!$request->isXmlHttpRequest()) {
            return $this->redirectToRoute('pim_enrich_associationtype_index');
        }

        $associationType = new AssociationType();

        if ($this->assocTypeHandler->process($associationType)) {
            $this->addFlash('success', 'flash.association type.created');

            $response = [
                'status' => 1,
                'url'    => $this->generateUrl('pim_enrich_associationtype_edit', ['id' => $associationType->getId()])
            ];

            return new Response(json_encode($response));
        }

        return [
            'form' => $this->assocTypeForm->createView(),
        ];
    }

    /**
     * Edit an association type
     *
     * @param Request $request
     * @param int     $id
     *
     * @Template
     * @AclAncestor("pim_enrich_associationtype_edit")
     *
     * @return array
     */
    public function editAction(Request $request, $id)
    {
        $associationType = $this->findOr404('PimCatalogBundle:AssociationType', $id);

        if ($this->assocTypeHandler->process($associationType)) {
            $this->addFlash('success', 'flash.association type.updated');

            return $this->redirectToRoute('pim_enrich_associationtype_edit', ['id' => $id]);
        }
        $usageCount = $this->assocManager->countForAssociationType($associationType);

        return [
            'form'       => $this->assocTypeForm->createView(),
            'usageCount' => $usageCount
        ];
    }

    /**
     * Remove an association type
     *
     * @param AssociationType $associationType
     *
     * @AclAncestor("pim_enrich_associationtype_remove")
     *
     * @return Response|RedirectResponse
     */
    public function removeAction(AssociationType $associationType)
    {
        $this->assocTypeRemover->remove($associationType);

        if ($this->getRequest()->isXmlHttpRequest()) {
            return new Response('', 204);
        } else {
            return $this->redirectToRoute('pim_enrich_associationtype_index');
        }
    }
}
