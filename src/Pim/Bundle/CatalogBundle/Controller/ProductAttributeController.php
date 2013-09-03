<?php

namespace Pim\Bundle\CatalogBundle\Controller;

use Pim\Bundle\CatalogBundle\AbstractController\AbstractDoctrineController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Validator\ValidatorInterface;
use Oro\Bundle\GridBundle\Renderer\GridRenderer;
use Pim\Bundle\CatalogBundle\Datagrid\DatagridWorkerInterface;
use Pim\Bundle\CatalogBundle\Form\Handler\ProductAttributeHandler;
use Symfony\Component\Form\Form;
use Pim\Bundle\CatalogBundle\Manager\LocaleManager;
use Pim\Bundle\CatalogBundle\Manager\ProductManager;
use Pim\Bundle\VersioningBundle\Manager\AuditManager;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Pim\Bundle\CatalogBundle\Entity\ProductAttribute;

/**
 * Product attribute controller
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductAttributeController extends AbstractDoctrineController
{
    private $gridRenderer;
    private $datagridWorker;
    private $attributeHandler;
    private $attributeForm;
    private $productManager;
    private $localeManager;
    private $auditManager;
    private $measuresConfig;
    
    public function __construct(
        Request $request,
        EngineInterface $templating,
        RouterInterface $router,
        SecurityContextInterface $securityContext,
        RegistryInterface $doctrine,
        FormFactoryInterface $formFactory,
        ValidatorInterface $validator,
        GridRenderer $gridRenderer,
        DatagridWorkerInterface $datagridWorker,
        ProductAttributeHandler $attributeHandler,
        Form $attributeForm,
        ProductManager $productManager,
        LocaleManager $localeManager,
        AuditManager $auditManager,
        $measuresConfig
    ) {
        parent::__construct($request, $templating, $router, $securityContext, $doctrine, $formFactory, $validator);
        $this->gridRenderer = $gridRenderer;
        $this->datagridWorker = $datagridWorker;
        $this->attributeHandler = $attributeHandler;
        $this->attributeForm = $attributeForm;
        $this->productManager = $productManager;
        $this->localeManager = $localeManager;
        $this->auditManager = $auditManager;
        $this->measuresConfig = $measuresConfig;
    }
    /**
     * List product attributes
     * @param Request $request
     *
     * @return template
     */
    public function indexAction(Request $request)
    {
        $datagrid = $this->datagridWorker->getDatagrid('productattribute');

        if ('json' == $request->getRequestFormat()) {
            $view = 'OroGridBundle:Datagrid:list.json.php';
        } else {
            $view = 'PimCatalogBundle:ProductAttribute:index.html.twig';
        }

        return $this->render($view, array('datagrid' => $datagrid->createView()));
    }

    /**
     * Create attribute
     *
     * @Template("PimCatalogBundle:ProductAttribute:form.html.twig")
     * @return array
     */
    public function createAction()
    {
        $attribute = $this->productManager->createAttribute('pim_catalog_text');

        if ($this->attributeHandler->process($attribute)) {
            $this->addFlash('success', 'Attribute successfully created');

            return $this->redirectToRoute('pim_catalog_productattribute_edit', array('id' => $attribute->getId()));
        }

        return array(
            'form'            => $this->attributeForm->createView(),
            'locales'         => $this->localeManager->getActiveLocales(),
            'disabledLocales' => $this->localeManager->getDisabledLocales(),
            'measures'        => $this->measuresConfig
        );
    }

    /**
     * Edit attribute form
     *
     * @param Request          $request
     * @param ProductAttribute $attribute
     *
     * @Template("PimCatalogBundle:ProductAttribute:form.html.twig")
     * @return array
     */
    public function editAction(Request $request, ProductAttribute $attribute)
    {
        if ($this->attributeHandler->process($attribute)) {
            $this->addFlash('success', 'Attribute successfully saved');

            return $this->redirectToRoute('pim_catalog_productattribute_edit', array('id' => $attribute->getId()));
        }

        $datagrid = $this->datagridWorker->getDataAuditDatagrid(
            $attribute,
            'pim_catalog_productattribute_edit',
            array('id' => $attribute->getId())
        );
        $datagridView = $datagrid->createView();

        if ('json' == $request->getRequestFormat()) {
            return $this->gridRenderer->renderResultsJsonResponse($datagridView);
        }

        return array(
            'form'            => $this->attributeForm->createView(),
            'locales'         => $this->localeManager->getActiveLocales(),
            'disabledLocales' => $this->localeManager->getDisabledLocales(),
            'measures'        => $this->measuresConfig,
            'datagrid'        => $datagridView,
            'created'         => $this->auditManager->getFirstLogEntry($attribute),
            'updated'         => $this->auditManager->getLastLogEntry($attribute),
        );
    }

    /**
     * Preprocess attribute form
     *
     * @param Request $request
     *
     * @Template("PimCatalogBundle:ProductAttribute:_form_parameters.html.twig")
     * @return array
     */
    public function preProcessAction(Request $request)
    {
        $data = $request->request->all();
        if (!isset($data['pim_catalog_attribute_form'])) {
            return $this->redirectToRoute('pim_catalog_productattribute_create');
        }

        // Add custom fields to the form and set the entered data to the form
        $this->attributeHandler->preProcess($data['pim_catalog_attribute_form']);

        $locales         = $this->localeManager->getActiveLocales();
        $disabledLocales = $this->localeManager->getDisabledLocales();
        $form            = $this->attributeForm->createView();

        $data = array(
            'parameters' => $this->renderView(
                'PimCatalogBundle:ProductAttribute:_form_parameters.html.twig',
                array(
                    'form'            => $form,
                    'locales'         => $locales,
                    'disabledLocales' => $disabledLocales
                )
            ),
            'values' => $this->renderView(
                'PimCatalogBundle:ProductAttribute:_form_values.html.twig',
                array(
                    'form'            => $form,
                    'locales'         => $locales,
                    'disabledLocales' => $disabledLocales
                )
            )
        );

        return new JsonResponse($data);
    }

    /**
     * Edit ProductAttribute sort order
     *
     * @param Request $request
     *
     * @return Response
     */
    public function sortAction(Request $request)
    {
        if (!$request->isXmlHttpRequest()) {
            return $this->redirectToRoute('pim_catalog_productattribute_index');
        }

        $data = $request->request->all();

        if (!empty($data)) {
            foreach ($data as $id => $sort) {
                $attribute = $this->getRepository('PimCatalogBundle:ProductAttribute')->find((int) $id);
                if ($attribute) {
                    $attribute->setSortOrder((int) $sort);
                    $this->getManager()->persist($attribute);
                }
            }
            $this->getManager()->flush();

            return new Response(1);
        }

        return new Response(0);
    }

    /**
     * Remove attribute
     *
     * @param Request          $request
     * @param ProductAttribute $entity
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     */
    public function removeAction(Request $request, ProductAttribute $entity)
    {
        if ($entity->getAttributeType() === 'pim_catalog_identifier') {
            if ($request->isXmlHttpRequest()) {
                return new Response('', 403);
            } else {
                return $this->redirectToRoute('pim_catalog_productattribute_index');
            }
        }

        $this->getManager()->remove($entity);
        $this->getManager()->flush();

        if ($request->isXmlHttpRequest()) {
            return new Response('', 204);
        } else {
            return $this->redirectToRoute('pim_catalog_productattribute_index');
        }
    }
}
