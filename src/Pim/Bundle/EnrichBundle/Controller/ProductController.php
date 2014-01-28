<?php

namespace Pim\Bundle\EnrichBundle\Controller;

use Pim\Bundle\CatalogBundle\Exception\MediaManagementException;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Validator\ValidatorInterface;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\StreamedResponse;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Oro\Bundle\SecurityBundle\SecurityFacade;
use Oro\Bundle\DataGridBundle\Extension\MassAction\MassActionParametersParser;
use Oro\Bundle\DataGridBundle\Extension\MassAction\MassActionDispatcher;

use Pim\Bundle\EnrichBundle\AbstractController\AbstractDoctrineController;
use Pim\Bundle\CatalogBundle\Entity\Category;
use Pim\Bundle\CatalogBundle\Manager\CategoryManager;
use Pim\Bundle\UserBundle\Context\UserContext;
use Pim\Bundle\CatalogBundle\Manager\ProductManager;
use Pim\Bundle\CatalogBundle\Model\AvailableAttributes;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\VersioningBundle\Manager\AuditManager;
use Pim\Bundle\EnrichBundle\Exception\DeleteException;

/**
 * Product Controller
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductController extends AbstractDoctrineController
{
    /**
     * @var ProductManager
     */
    protected $productManager;

    /**
     * @var CategoryManager
     */
    protected $categoryManager;

    /**
     * @var UserContext
     */
    protected $userContext;

    /**
     * @var AuditManager
     */
    protected $auditManager;

    /**
     * @var SecurityFacade
     */
    protected $securityFacade;

    /**
     * @var MassActionParametersParser
     */
    protected $parametersParser;

    /**
     * @var MassActionDispatcher
     */
    protected $massActionDispatcher;

    /**
     * Constant used to redirect to the datagrid when save edit form
     * @staticvar string
     */
    const BACK_TO_GRID = 'BackGrid';

    /**
     * Constant used to redirect to create popin when save edit form
     * @staticvar string
     */
    const CREATE       = 'Create';

    /**
     * Constructor
     *
     * @param Request                    $request
     * @param EngineInterface            $templating
     * @param RouterInterface            $router
     * @param SecurityContextInterface   $securityContext
     * @param FormFactoryInterface       $formFactory
     * @param ValidatorInterface         $validator
     * @param TranslatorInterface        $translator
     * @param RegistryInterface          $doctrine
     * @param ProductManager             $productManager
     * @param CategoryManager            $categoryManager
     * @param UserContext                $userContext
     * @param AuditManager               $auditManager
     * @param SecurityFacade             $securityFacade
     * @param MassActionParametersParser $parametersParser
     * @param MassActionDispatcher       $massActionDispatcher
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
        ProductManager $productManager,
        CategoryManager $categoryManager,
        UserContext $userContext,
        AuditManager $auditManager,
        SecurityFacade $securityFacade,
        MassActionParametersParser $parametersParser,
        MassActionDispatcher $massActionDispatcher
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

        $this->productManager       = $productManager;
        $this->categoryManager      = $categoryManager;
        $this->userContext          = $userContext;
        $this->auditManager         = $auditManager;
        $this->securityFacade       = $securityFacade;
        $this->parametersParser     = $parametersParser;
        $this->massActionDispatcher = $massActionDispatcher;

        $this->productManager->setLocale($this->getDataLocale());
    }

    /**
     * List products
     *
     * @param Request $request the request
     *
     * @AclAncestor("pim_enrich_product_index")
     * @Template
     * @return Response
     */
    public function indexAction(Request $request)
    {
        if ('csv' === $request->getRequestFormat()) {
            // Export time execution depends on entities exported
            ignore_user_abort(false);
            set_time_limit(0);

            $parameters  = $this->parametersParser->parse($request);
            $requestData = array_merge($request->query->all(), $request->request->all());

            $result = $this->massActionDispatcher->dispatch(
                $requestData['gridName'],
                $requestData['actionName'],
                $parameters,
                $requestData
            );

            $dateTime = new \DateTime();
            $fileName = sprintf(
                'products_export_%s_%s_%s.csv',
                $this->getDataLocale(),
                $this->productManager->getScope(),
                $dateTime->format('Y-m-d_H:i:s')
            );

            // prepare response
            $response = new StreamedResponse();
            $attachment = $response->headers->makeDisposition(ResponseHeaderBag::DISPOSITION_INLINE, $fileName);
            $response->headers->set('Content-Type', 'text/csv');
            $response->headers->set('Content-Disposition', $attachment);
            $response->setCallback($this->quickExportCallback($result));

            return $response->send();
        }

        return array(
            'locales'    => $this->userContext->getUserLocales(),
            'dataLocale' => $this->getDataLocale(),
        );
    }

    /**
     * Quick export callback
     *
     * @param string $result
     *
     * @return \Closure
     */
    protected function quickExportCallback($result)
    {
        return function () use ($result) {
            echo $result;
            flush();
        };
    }

    /**
     * Create product
     *
     * @param Request $request
     * @param string  $dataLocale
     *
     * @Template
     * @AclAncestor("pim_enrich_product_create")
     * @return array
     */
    public function createAction(Request $request, $dataLocale)
    {
        if (!$request->isXmlHttpRequest()) {
            return $this->redirectToRoute('pim_enrich_product_index');
        }

        $entity = $this->productManager->createProduct();
        $form = $this->createForm('pim_product_create', $entity, $this->getCreateFormOptions($entity));
        if ($request->isMethod('POST')) {
            $form->submit($request);
            if ($form->isValid()) {
                $this->productManager->save($entity);
                $this->addFlash('success', 'flash.product.created');

                if ($dataLocale === null) {
                    $dataLocale = $this->getDataLocale();
                }
                $url = $this->generateUrl(
                    'pim_enrich_product_edit',
                    array('id' => $entity->getId(), 'dataLocale' => $dataLocale)
                );
                $response = array('status' => 1, 'url' => $url);

                return new Response(json_encode($response));
            }
        }

        return array(
            'form'       => $form->createView(),
            'dataLocale' => $this->getDataLocale()
        );
    }

    /**
     * Edit product
     *
     * @param Request $request
     * @param integer $id
     *
     * @Template
     * @AclAncestor("pim_enrich_product_edit")
     * @return array
     */
    public function editAction(Request $request, $id)
    {
        $product = $this->findProductOr404($id);

        $this->productManager->ensureAllAssociationTypes($product);

        $form = $this->createForm(
            'pim_product_edit',
            $product,
            $this->getEditFormOptions($product)
        );

        if ($request->isMethod('POST')) {
            $form->submit($request, false);

            if ($form->isValid()) {
                try {
                    $this->productManager->handleMedia($product);
                    $this->productManager->save($product);

                    $this->addFlash('success', 'flash.product.updated');
                } catch (MediaManagementException $e) {
                    $this->addFlash('error', $e->getMessage());
                }

                // TODO : Check if the locale exists and is activated
                $params = array('id' => $product->getId(), 'dataLocale' => $this->getDataLocale());
                if ($comparisonLocale = $this->getComparisonLocale()) {
                    $params['compareWith'] = $comparisonLocale;
                }

                return $this->redirectAfterEdit($params);
            } else {
                $this->addFlash('error', 'flash.product.invalid');
            }
        }

        $channels = $this->getRepository('PimCatalogBundle:Channel')->findAll();
        $trees    = $this->productManager->getFlexibleRepository()->getProductCountByTree($product);

        return array(
            'form'             => $form->createView(),
            'dataLocale'       => $this->getDataLocale(),
            'comparisonLocale' => $this->getComparisonLocale(),
            'channels'         => $channels,
            'attributesForm'   =>
                $this->getAvailableAttributesForm($product->getAttributes())->createView(),
            'product'          => $product,
            'trees'            => $trees,
            'created'          => $this->auditManager->getOldestLogEntry($product),
            'updated'          => $this->auditManager->getNewestLogEntry($product),
            'locales'          => $this->userContext->getUserLocales(),
            'createPopin'      => $this->getRequest()->get('create_popin')
        );
    }

    /**
     * Switch case to redirect after saving a product from the edit form
     *
     * @param array $params
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function redirectAfterEdit($params)
    {
        switch ($this->getRequest()->get('action')) {
            case self::BACK_TO_GRID:
                $route = 'pim_enrich_product_index';
                $params = array();
                break;
            case self::CREATE:
                $route = 'pim_enrich_product_edit';
                $params['create_popin'] = true;
                break;
            default:
                $route = 'pim_enrich_product_edit';
                break;
        }

        return $this->redirectToRoute($route, $params);
    }

    /**
     * History of a product
     *
     * @param Request $request
     * @param integer $id
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function historyAction(Request $request, $id)
    {
        return $this->render(
            'PimEnrichBundle:Product:_history.html.twig',
            array(
                'product' => $this->findProductOr404($id),
            )
        );
    }

    /**
     * Add attributes to product
     *
     * @param Request $request The request object
     * @param integer $id      The product id to which add attributes
     *
     * @AclAncestor("pim_enrich_product_add_attribute")
     * @return Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function addAttributesAction(Request $request, $id)
    {
        $product             = $this->findProductOr404($id);
        $availableAttributes = new AvailableAttributes();
        $attributesForm      = $this->getAvailableAttributesForm(
            $product->getAttributes(),
            $availableAttributes
        );
        $attributesForm->submit($request);

        foreach ($availableAttributes->getAttributes() as $attribute) {
            $this->productManager->addAttributeToProduct($product, $attribute);
        }

        $this->productManager->save($product);

        $this->addFlash('success', 'flash.product.attributes added');

        return $this->redirectToRoute('pim_enrich_product_edit', array('id' => $product->getId()));
    }

    /**
     * Remove product
     *
     * @param Request $request
     * @param integer $id
     *
     * @AclAncestor("pim_enrich_product_remove")
     * @return Response|RedirectResponse
     */
    public function removeAction(Request $request, $id)
    {
        $product = $this->findProductOr404($id);
        $this->getManager()->remove($product);
        $this->getManager()->flush();
        if ($request->isXmlHttpRequest()) {
            return new Response('', 204);
        } else {
            return $this->redirectToRoute('pim_enrich_product_index');
        }
    }

    /**
     * Remove an attribute form a product
     *
     * @param integer $productId
     * @param integer $attributeId
     *
     * @AclAncestor("pim_enrich_product_remove_attribute")
     * @return RedirectResponse
     *
     * @throws NotFoundHttpException
     */
    public function removeAttributeAction($productId, $attributeId)
    {
        $product   = $this->findOr404('Pim\Bundle\CatalogBundle\Model\Product', $productId);
        $attribute = $this->findOr404($this->productManager->getAttributeName(), $attributeId);

        if ($product->isAttributeRemovable($attribute)) {
            $this->productManager->removeAttributeFromProduct($product, $attribute);
        } else {
            throw new DeleteException($this->getTranslator()->trans('product.attribute not removable'));
        }
        if ($this->getRequest()->isXmlHttpRequest()) {
            return new Response('', 204);
        } else {
            return $this->redirectToRoute('pim_enrich_product_edit', array('id' => $productId));
        }
    }

    /**
     * List categories associated with the provided product and descending from the category
     * defined by the parent parameter.
     *
     * @param Request  $request The request object
     * @param integer  $id      Product id
     * @param Category $parent  The parent category
     *
     * httpparam include_category if true, will include the parentCategory in the response
     *
     * @ParamConverter("parent", class="PimCatalogBundle:Category", options={"id" = "category_id"})
     * @Template
     * @AclAncestor("pim_enrich_product_categories_view")
     * @return array
     */
    public function listCategoriesAction(Request $request, $id, Category $parent)
    {
        $product = $this->findProductOr404($id);
        $categories = null;

        $includeParent = $request->get('include_parent', false);
        $includeParent = ($includeParent === 'true');

        if ($product != null) {
            $categories = $product->getCategories();
        }
        $trees = $this->categoryManager->getFilledTree($parent, $categories);

        return array('trees' => $trees, 'categories' => $categories);
    }

    /**
     * {@inheritdoc}
     */
    protected function redirectToRoute($route, $parameters = array(), $status = 302)
    {
        if (!isset($parameters['dataLocale'])) {
            $parameters['dataLocale'] = $this->getDataLocale();
        }

        return parent::redirectToRoute($route, $parameters, $status);
    }

    /**
     * Get data locale code
     *
     * @throws \Exception
     *
     * @return string
     */
    protected function getDataLocale()
    {
        $dataLocale = $this->getRequest()->get('dataLocale');
        if ($dataLocale === null) {
            $catalogLocale = $this->getUser()->getCatalogLocale();
            if ($catalogLocale) {
                $dataLocale = $catalogLocale->getCode();
            }
        }
        if (!$dataLocale) {
            throw new \Exception('User must have a catalog locale defined');
        }
        if (!$this->securityFacade->isGranted('pim_enrich_locale_'.$dataLocale)) {
            throw new \Exception(sprintf("User doesn't have access to the locale '%s'", $dataLocale));
        }

        return $dataLocale;
    }

    /**
     * @return string
     */
    protected function getComparisonLocale()
    {
        $locale = $this->getRequest()->query->get('compareWith');

        if ($this->getDataLocale() !== $locale) {
            return $locale;
        }
    }

    /**
     * Get data scope
     *
     * @throws \Exception
     *
     * @return string
     */
    protected function getDataScope()
    {
        $dataScope = $this->getRequest()->get('dataScope');
        if ($dataScope === null) {
            $catalogScope = $this->getUser()->getCatalogScope();
            if ($catalogScope) {
                $dataScope = $catalogScope->getCode();
            }
        }
        if (!$dataScope) {
            throw new \Exception('User must have a catalog scope defined');
        }

        return $dataScope;
    }

    /**
     * Find a product by its id or return a 404 response
     *
     * @param integer $id the product id
     *
     * @return Pim\Bundle\CatalogBundle\Model\ProductInterface
     *
     * @throws Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    protected function findProductOr404($id)
    {
        $product = $this->productManager->find($id);

        if (!$product) {
            throw $this->createNotFoundException(
                sprintf('Product with id %s could not be found.', (string) $id)
            );
        }

        return $product;
    }

    /**
     * Get the AvailbleAttributes form
     *
     * @param array               $attributes          The attributes
     * @param AvailableAttributes $availableAttributes The available attributes container
     *
     * @return Symfony\Component\Form\Form
     */
    protected function getAvailableAttributesForm(
        array $attributes = array(),
        AvailableAttributes $availableAttributes = null
    ) {
        return $this->createForm(
            'pim_available_attributes',
            $availableAttributes ?: new AvailableAttributes(),
            array('attributes' => $attributes)
        );
    }

    /**
     * Returns the options for the edit form
     *
     * @param ProductInterface $product
     *
     * @return array
     */
    protected function getEditFormOptions(ProductInterface $product)
    {
        return array(
            'enable_family'    => $this->securityFacade->isGranted('pim_enrich_product_change_family'),
            'enable_state'     => $this->securityFacade->isGranted('pim_enrich_product_change_state'),
            'currentLocale'    => $this->getDataLocale(),
            'comparisonLocale' => $this->getComparisonLocale(),
        );
    }

    /**
     * Returns the options for the create form
     *
     * @param ProductInterface $product
     *
     * @return array
     */
    protected function getCreateFormOptions(ProductInterface $product)
    {
        return array();
    }
}
