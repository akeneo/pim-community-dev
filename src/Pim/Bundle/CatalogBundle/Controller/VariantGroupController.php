<?php

namespace Pim\Bundle\CatalogBundle\Controller;

use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\ValidatorInterface;
use Symfony\Component\Translation\TranslatorInterface;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;

use Pim\Bundle\CatalogBundle\AbstractController\AbstractDoctrineController;
use Pim\Bundle\CatalogBundle\Datagrid\DatagridWorkerInterface;
use Pim\Bundle\CatalogBundle\Entity\VariantGroup;
use Pim\Bundle\CatalogBundle\Form\Handler\VariantGroupHandler;

/**
 * Variant group controller
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class VariantGroupController extends AbstractDoctrineController
{
    /**
     * @var DatagridWorkerInterface
     */
    protected $datagridWorker;

    /**
     * @var VariantGroupHandler
     */
    protected $variantHandler;

    /**
     * @var Form
     */
    protected $variantForm;

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
     * @param DatagridWorkerInterface  $datagridWorker
     * @param VariantGroupHandler      $variantHandler
     * @param Form                     $variantForm
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
        DatagridWorkerInterface $datagridWorker,
        VariantGroupHandler $variantHandler,
        Form $variantForm
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

        $this->datagridWorker = $datagridWorker;
        $this->variantHandler = $variantHandler;
        $this->variantForm    = $variantForm;
    }

    /**
     * List variant groups
     *
     * @param Request $request
     *
     * @Template
     * @AclAncestor("pim_catalog_variant_group_index")
     * @return Response
     */
    public function indexAction(Request $request)
    {
        /** @var QueryBuilder $queryBuilder */
        $queryBuilder = $this->getManager()->createQueryBuilder();
        $queryBuilder
            ->select('v')
            ->from('PimCatalogBundle:VariantGroup', 'v');
        $datagrid = $this->datagridWorker->getDatagrid('variant_group', $queryBuilder);

        $view = ('json' === $request->getRequestFormat())
            ? 'OroGridBundle:Datagrid:list.json.php'
            : 'PimCatalogBundle:VariantGroup:index.html.twig';

        return $this->render($view, array('datagrid' => $datagrid->createView()));
    }

    /**
     * Create a variant group
     * @param Request $request
     *
     * @Template
     * @AclAncestor("pim_catalog_variant_group_create")
     * @return Response|RedirectResponse
     */
    public function createAction(Request $request)
    {
        if (!$request->isXmlHttpRequest()) {
            return $this->redirectToRoute('pim_catalog_variant_group_index');
        }

        $variant = new VariantGroup();

        if ($this->variantHandler->process($variant)) {
            $this->addFlash('success', 'flash.variant group.created');

            $url = $this->generateUrl(
                'pim_catalog_variant_group_edit',
                array('id' => $variant->getId())
            );
            $response = array('status' => 1, 'url' => $url);

            return new Response(json_encode($response));
        }

        return array(
            'form' => $this->variantForm->createView()
        );
    }

    /**
     * Edit a variant group
     *
     * @param VariantGroup $variant
     *
     * @Template
     * @AclAncestor("pim_catalog_variant_group_edit")
     * @return array
     */
    public function editAction(VariantGroup $variant)
    {
        if ($this->variantHandler->process($variant)) {
            $this->addFlash('success', 'flash.variant group.updated');
        }

        $datagridManager = $this->datagridWorker->getDatagridManager('variant_product');
        $datagridManager->setVariantGroup($variant);
        $datagridView = $datagridManager->getDatagrid()->createView();

        if ('json' === $this->getRequest()->getRequestFormat()) {
            return $this->render(
                'OroGridBundle:Datagrid:list.json.php',
                array('datagrid' => $datagridView)
            );
        }

        return array(
            'form' => $this->variantForm->createView(),
            'datagrid' => $datagridView
        );
    }

    /**
     * Remove a variant group
     * @param VariantGroup $variant
     *
     * @AclAncestor("pim_catalog_variant_group_remove")
     * @return Response|RedirectResponse
     */
    public function removeAction(VariantGroup $variant)
    {
        $this->getManager()->remove($variant);
        $this->getManager()->flush();

        if ($this->getRequest()->isXmlHttpRequest()) {
            return new Response('', 204);
        } else {
            return $this->redirectToRoute('pim_catalog_variant_group_index');
        }
    }
}
