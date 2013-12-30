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
use Pim\Bundle\CatalogBundle\Entity\AssociationType;
use Pim\Bundle\CatalogBundle\Form\Handler\AssociationTypeHandler;

/**
 * Association type controller
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AssociationTypeController extends AbstractDoctrineController
{
    /**
     * @var LocaleManager
     */
    private $localeManager;

    /**
     * @var AssociationTypeHandler
     */
    protected $associationTypeHandler;

    /**
     * @var Form
     */
    protected $associationTypeForm;

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
     * @param AssociationTypeHandler   $associationTypeHandler
     * @param Form                     $associationTypeForm
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
        AssociationTypeHandler $associationTypeHandler,
        Form $associationTypeForm
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

        $this->localeManager          = $localeManager;
        $this->associationTypeHandler = $associationTypeHandler;
        $this->associationTypeForm    = $associationTypeForm;
    }

    /**
     * List association types
     *
     * @param Request $request
     *
     * @Template
     * @AclAncestor("pim_catalog_association_type_index")
     * @return Response
     */
    public function indexAction(Request $request)
    {
        return array(
            'localeCode' => $this->localeManager->getUserLocale()->getCode()
        );
    }

    /**
     * Create an association type
     *
     * @param Request $request
     *
     * @Template
     * @AclAncestor("pim_catalog_association_type_create")
     * @return Response|RedirectResponse
     */
    public function createAction(Request $request)
    {
        if (!$request->isXmlHttpRequest()) {
            return $this->redirectToRoute('pim_catalog_association_type_index');
        }

        $associationType = new AssociationType();

        if ($this->associationTypeHandler->process($associationType)) {
            $this->addFlash('success', 'flash.association type.created');

            $response = array(
                'status' => 1,
                'url' =>
                    $this->generateUrl('pim_catalog_association_type_edit', array('id' => $associationType->getId()))
            );

            return new Response(json_encode($response));
        }

        return array(
            'form' => $this->associationTypeForm->createView(),
        );
    }

    /**
     * Edit an association type
     *
     * @param Request $request
     * @param integer $id
     *
     * @Template
     * @AclAncestor("pim_catalog_association_type_edit")
     * @return array
     */
    public function editAction(Request $request, $id)
    {
        $associationType = $this->findOr404('PimCatalogBundle:AssociationType', $id);

        if ($this->associationTypeHandler->process($associationType)) {
            $this->addFlash('success', 'flash.association type.updated');

            return $this->redirectToRoute('pim_catalog_association_type_edit', array('id' => $id));
        }

        $usageCount = $this
            ->getRepository('PimCatalogBundle:ProductAssociation')
            ->countForAssociationType($associationType);

        return array(
            'form'       => $this->associationTypeForm->createView(),
            'usageCount' => $usageCount
        );
    }

    /**
     * Remove an association type
     *
     * @param AssociationType $associationType
     *
     * @AclAncestor("pim_catalog_association_type_remove")
     * @return Response|RedirectResponse
     */
    public function removeAction(AssociationType $associationType)
    {
        $this->getManager()->remove($associationType);
        $this->getManager()->flush();

        if ($this->getRequest()->isXmlHttpRequest()) {
            return new Response('', 204);
        } else {
            return $this->redirectToRoute('pim_catalog_association_type_index');
        }
    }
}
