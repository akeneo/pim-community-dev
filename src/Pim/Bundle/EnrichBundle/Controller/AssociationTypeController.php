<?php

namespace Pim\Bundle\EnrichBundle\Controller;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Validator\ValidatorInterface;
use Doctrine\Common\Persistence\ManagerRegistry;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;

use Pim\Bundle\EnrichBundle\AbstractController\AbstractDoctrineController;
use Pim\Bundle\CatalogBundle\Entity\AssociationType;
use Pim\Bundle\CatalogBundle\Manager\AssociationManager;
use Pim\Bundle\CatalogBundle\Manager\AssociationTypeManager;
use Pim\Bundle\EnrichBundle\Form\Handler\AssociationTypeHandler;

/**
 * Association type controller
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AssociationTypeController extends AbstractDoctrineController
{
    /** @var AssociationTypeHandler */
    protected $assocTypeHandler;

    /** @var Form */
    protected $assocTypeForm;

    /** @var AssociationTypeManager */
    protected $assocTypeManager;

    /** @var AssociationManager */
    protected $assocManager;

    /**
     * Constructor
     *
     * @param Request                  $request
     * @param EngineInterface          $templating
     * @param RouterInterface          $router
     * @param SecurityContextInterface $securityContext
     * @param FormFactoryInterface     $formFactory
     * @param ValidatorInterface       $validator
     * @param TranslatorInterface      $translator
     * @param EventDispatcherInterface $eventDispatcher
     * @param ManagerRegistry          $doctrine
     * @param AssociationTypeManager   $assocTypeManager
     * @param AssociationManager       $assocManager
     * @param AssociationTypeHandler   $assocTypeHandler
     * @param Form                     $assocTypeForm
     */
    public function __construct(
        Request $request,
        EngineInterface $templating,
        RouterInterface $router,
        SecurityContextInterface $securityContext,
        FormFactoryInterface $formFactory,
        ValidatorInterface $validator,
        TranslatorInterface $translator,
        EventDispatcherInterface $eventDispatcher,
        ManagerRegistry $doctrine,
        AssociationTypeManager $assocTypeManager,
        AssociationManager $assocManager,
        AssociationTypeHandler $assocTypeHandler,
        Form $assocTypeForm
    ) {
        parent::__construct(
            $request,
            $templating,
            $router,
            $securityContext,
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
    }

    /**
     * List association types
     *
     * @param Request $request
     *
     * @Template
     * @AclAncestor("pim_enrich_association_type_index")
     * @return Response
     */
    public function indexAction(Request $request)
    {
        return array();
    }

    /**
     * Create an association type
     *
     * @param Request $request
     *
     * @Template
     * @AclAncestor("pim_enrich_association_type_create")
     * @return Response|RedirectResponse
     */
    public function createAction(Request $request)
    {
        if (!$request->isXmlHttpRequest()) {
            return $this->redirectToRoute('pim_enrich_association_type_index');
        }

        $associationType = new AssociationType();

        if ($this->assocTypeHandler->process($associationType)) {
            $this->addFlash('success', 'flash.association type.created');

            $response = array(
                'status' => 1,
                'url' =>
                    $this->generateUrl('pim_enrich_association_type_edit', array('id' => $associationType->getId()))
            );

            return new Response(json_encode($response));
        }

        return array(
            'form' => $this->assocTypeForm->createView(),
        );
    }

    /**
     * Edit an association type
     *
     * @param Request $request
     * @param integer $id
     *
     * @Template
     * @AclAncestor("pim_enrich_association_type_edit")
     * @return array
     */
    public function editAction(Request $request, $id)
    {
        $associationType = $this->findOr404('PimCatalogBundle:AssociationType', $id);

        if ($this->assocTypeHandler->process($associationType)) {
            $this->addFlash('success', 'flash.association type.updated');

            return $this->redirectToRoute('pim_enrich_association_type_edit', array('id' => $id));
        }
        $usageCount = $this->assocManager->countForAssociationType($associationType);

        return array(
            'form'       => $this->assocTypeForm->createView(),
            'usageCount' => $usageCount
        );
    }

    /**
     * Remove an association type
     *
     * @param AssociationType $associationType
     *
     * @AclAncestor("pim_enrich_association_type_remove")
     * @return Response|RedirectResponse
     */
    public function removeAction(AssociationType $associationType)
    {
        $this->assocTypeManager->remove($associationType);

        if ($this->getRequest()->isXmlHttpRequest()) {
            return new Response('', 204);
        } else {
            return $this->redirectToRoute('pim_enrich_association_type_index');
        }
    }
}
