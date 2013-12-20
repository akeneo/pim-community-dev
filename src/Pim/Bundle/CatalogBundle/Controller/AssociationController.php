<?php

namespace Pim\Bundle\CatalogBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Validator\ValidatorInterface;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;

use Pim\Bundle\CatalogBundle\AbstractController\AbstractDoctrineController;
use Pim\Bundle\CatalogBundle\Manager\LocaleManager;
use Pim\Bundle\CatalogBundle\Entity\Association;
use Pim\Bundle\CatalogBundle\Form\Handler\AssociationHandler;

/**
 * Association controller
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AssociationController extends AbstractDoctrineController
{
    /**
     * @var LocaleManager
     */
    private $localeManager;

    /**
     * @var AssociationHandler
     */
    protected $associationHandler;

    /**
     * @var Form
     */
    protected $associationForm;

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
     * @param RegistryInterface        $doctrine
     * @param LocaleManager            $localeManager
     * @param AssociationHandler       $associationHandler
     * @param Form                     $associationForm
     */
    public function __construct(
        Request $request,
        EngineInterface $templating,
        RouterInterface $router,
        SecurityContextInterface $securityContext,
        FormFactoryInterface $formFactory,
        ValidatorInterface $validator,
        TranslatorInterface $translator,
        RegistryInterface $doctrine,
        LocaleManager $localeManager,
        AssociationHandler $associationHandler,
        Form $associationForm
    ) {
        parent::__construct(
            $request,
            $templating,
            $router,
            $securityContext,
            $formFactory,
            $validator,
            $translator,
            $doctrine
        );

        $this->localeManager      = $localeManager;
        $this->associationHandler = $associationHandler;
        $this->associationForm    = $associationForm;
    }

    /**
     * List associations
     *
     * @param Request $request
     *
     * @Template
     * @AclAncestor("pim_catalog_association_index")
     * @return Response
     */
    public function indexAction(Request $request)
    {
        return array(
            'localeCode' => $this->localeManager->getUserLocale()->getCode()
        );
    }

    /**
     * Create an association
     *
     * @param Request $request
     *
     * @Template
     * @AclAncestor("pim_catalog_association_create")
     * @return Response|RedirectResponse
     */
    public function createAction(Request $request)
    {
        if (!$request->isXmlHttpRequest()) {
            return $this->redirectToRoute('pim_catalog_association_index');
        }

        $association = new Association();

        if ($this->associationHandler->process($association)) {
            $this->addFlash('success', 'flash.association.created');

            $response = array(
                'status' => 1,
                'url' => $this->generateUrl('pim_catalog_association_edit', array('id' => $association->getId()))
            );

            return new Response(json_encode($response));
        }

        return array(
            'form' => $this->associationForm->createView(),
        );
    }

    /**
     * Edit an association
     *
     * @param Request $request
     * @param integer $id
     *
     * @Template
     * @AclAncestor("pim_catalog_association_edit")
     * @return array
     */
    public function editAction(Request $request, $id)
    {
        $association = $this->findOr404('PimCatalogBundle:Association', $id);

        if ($this->associationHandler->process($association)) {
            $this->addFlash('success', 'flash.association.updated');

            return $this->redirectToRoute('pim_catalog_association_edit', array('id' => $id));
        }

        $usageCount = $this->getRepository('PimCatalogBundle:ProductAssociation')->countForAssociation($association);

        return array(
            'form'       => $this->associationForm->createView(),
            'usageCount' => $usageCount
        );
    }

    /**
     * Remove an association
     *
     * @param Association $association
     *
     * @AclAncestor("pim_catalog_association_remove")
     * @return Response|RedirectResponse
     */
    public function removeAction(Association $association)
    {
        $this->getManager()->remove($association);
        $this->getManager()->flush();

        if ($this->getRequest()->isXmlHttpRequest()) {
            return new Response('', 204);
        } else {
            return $this->redirectToRoute('pim_catalog_association_index');
        }
    }
}
