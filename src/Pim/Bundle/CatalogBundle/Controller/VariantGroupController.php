<?php

namespace Pim\Bundle\CatalogBundle\Controller;

use Symfony\Component\HttpFoundation\Response;

use Symfony\Component\Form\Form;

use Pim\Bundle\CatalogBundle\Form\Handler\VariantGroupHandler;

use Pim\Bundle\CatalogBundle\Form\Handler\ChannelHandler;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Validator\ValidatorInterface;
use Symfony\Component\Translation\TranslatorInterface;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Oro\Bundle\UserBundle\Annotation\Acl;

use Pim\Bundle\CatalogBundle\AbstractController\AbstractDoctrineController;
use Pim\Bundle\CatalogBundle\Datagrid\DatagridWorkerInterface;
use Pim\Bundle\CatalogBundle\Entity\VariantGroup;

/**
 * Variant group controller
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @Acl(
 *     id="pim_catalog_variant_group",
 *     name="Variant group manipulation",
 *     description="Variant group manipulation",
 *     parent="pim_catalog"
 * )
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
     * @param Request $request
     * @param EngineInterface $templating
     * @param RouterInterface $router
     * @param SecurityContextInterface $securityContext
     * @param FormFactoryInterface $formFactory
     * @param ValidatorInterface $validator
     * @param TranslatorInterface $translator
     * @param RegistryInterface $doctrine
     * @param DatagridWorkerInterface $datagridWorker
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
     * @Acl(
     *     id="pim_catalog_variant_group_index",
     *     name="View variant group list",
     *     description="View variant group list",
     *     parent="pim_catalog_variant_group"
     * )
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
     *
     * @Template
     * @Acl(
     *     id="pim_catalog_variant_group_create",
     *     name="Create a variant group",
     *     description="Create a variant group",
     *     parent="pim_catalog_variant_group"
     * )
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
     * @Acl(
     *     id="pim_catalog_variant_group_edit",
     *     name="Edit a variant group",
     *     description="Edit a variant group",
     *     parent="pim_catalog_variant_group"
     * )
     * @return array
     */
    public function editAction(VariantGroup $variant)
    {
        return array();
    }

    /**
     * Remove a variant group
     *
     * @param VariantGroup $variant
     * @Acl(
     *     id="pim_catalog_variant_group_remove",
     *     name="Remove a variant group",
     *     description="Remove a variant group",
     *     parent="pim_catalog_variant_group"
     * )
     * @return Response
     */
    public function removeAction(VariantGroup $variant)
    {
    }
}
