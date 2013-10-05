<?php

namespace Pim\Bundle\CatalogBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Validator\ValidatorInterface;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Form\Form;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Oro\Bundle\GridBundle\Renderer\GridRenderer;

use Pim\Bundle\CatalogBundle\AbstractController\AbstractDoctrineController;
use Pim\Bundle\CatalogBundle\Datagrid\DatagridWorkerInterface;
use Pim\Bundle\CatalogBundle\Form\Handler\ProductAttributeHandler;
use Pim\Bundle\CatalogBundle\Manager\LocaleManager;
use Pim\Bundle\CatalogBundle\Manager\ProductManager;
use Pim\Bundle\VersioningBundle\Manager\AuditManager;
use Pim\Bundle\CatalogBundle\Entity\ProductAttribute;
use Pim\Bundle\CatalogBundle\Exception\DeleteException;

/**
 * Product attribute controller
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductAttributeController extends AbstractDoctrineController
{
    /**
     * @var GridRenderer
     */
    private $gridRenderer;

    /**
     * @var DatagridWorkerInterface
     */
    private $datagridWorker;

    /**
     * @var ProductAttributeHandler
     */
    private $attributeHandler;

    /**
     * @var Form
     */
    private $attributeForm;

    /**
     * @var ProductManager
     */
    private $productManager;

    /**
     * @var LocaleManager
     */
    private $localeManager;

    /**
     * @var AuditManager
     */
    private $auditManager;

    /**
     * @var array
     */
    private $measuresConfig;

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
     * @param GridRenderer             $gridRenderer
     * @param DatagridWorkerInterface  $datagridWorker
     * @param ProductAttributeHandler  $attributeHandler
     * @param Form                     $attributeForm
     * @param ProductManager           $productManager
     * @param LocaleManager            $localeManager
     * @param AuditManager             $auditManager
     * @param array                    $measuresConfig
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
        GridRenderer $gridRenderer,
        DatagridWorkerInterface $datagridWorker,
        ProductAttributeHandler $attributeHandler,
        Form $attributeForm,
        ProductManager $productManager,
        LocaleManager $localeManager,
        AuditManager $auditManager,
        $measuresConfig
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

        $this->gridRenderer     = $gridRenderer;
        $this->datagridWorker   = $datagridWorker;
        $this->attributeHandler = $attributeHandler;
        $this->attributeForm    = $attributeForm;
        $this->productManager   = $productManager;
        $this->localeManager    = $localeManager;
        $this->auditManager     = $auditManager;
        $this->measuresConfig   = $measuresConfig;
    }
    /**
     * List product attributes
     * @param Request $request
     *
     * @AclAncestor("pim_catalog_attribute_index")
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
     * @AclAncestor("pim_catalog_attribute_create")
     * @return array
     */
    public function createAction()
    {
        $attribute = $this->productManager->createAttribute('pim_catalog_text');

        if ($this->attributeHandler->process($attribute)) {
            $this->addFlash('success', 'flash.attribute.created');

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
     * @AclAncestor("pim_catalog_attribute_edit")
     * @return array
     */
    public function editAction(Request $request, ProductAttribute $attribute)
    {
        if ($this->attributeHandler->process($attribute)) {
            $this->addFlash('success', 'flash.attribute.updated');

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
            'created'         => $this->auditManager->getOldestLogEntry($attribute),
            'updated'         => $this->auditManager->getNewestLogEntry($attribute),
        );
    }

    /**
     * Preprocess attribute form
     *
     * @param Request $request
     *
     * @Template("PimCatalogBundle:ProductAttribute:_form_parameters.html.twig")
     * @AclAncestor("pim_catalog_attribute_edit")
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
     * @AclAncestor("pim_catalog_attribute_sort")
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
     * @AclAncestor("pim_catalog_attribute_remove")
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     */
    public function removeAction(Request $request, ProductAttribute $entity)
    {
        if ($entity->getAttributeType() === 'pim_catalog_identifier') {
            if ($request->isXmlHttpRequest()) {
                throw new DeleteException($this->getTranslator()->trans('flash.attribute.identifier not removable'));
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
