<?php

namespace Pim\Bundle\EnrichBundle\Controller;

use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Persistence\ManagerRegistry;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Oro\Bundle\SecurityBundle\SecurityFacade;
use Pim\Bundle\CatalogBundle\Exception\MediaManagementException;
use Pim\Bundle\CatalogBundle\Manager\CategoryManager;
use Pim\Bundle\CatalogBundle\Manager\ProductCategoryManager;
use Pim\Bundle\CatalogBundle\Manager\ProductManager;
use Pim\Bundle\CatalogBundle\Model\AvailableAttributes;
use Pim\Bundle\CatalogBundle\Model\CategoryInterface;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\EnrichBundle\AbstractController\AbstractDoctrineController;
use Pim\Bundle\EnrichBundle\Event\ProductEvents;
use Pim\Bundle\EnrichBundle\Exception\DeleteException;
use Pim\Bundle\EnrichBundle\Manager\SequentialEditManager;
use Pim\Bundle\UserBundle\Context\UserContext;
use Pim\Bundle\VersioningBundle\Manager\VersionManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Validator\ValidatorInterface;

/**
 * Product attribute controller, allows to add and remove optional attributes to a product
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductAttributeController extends AbstractDoctrineController
{
    /** @var ProductManager */
    protected $productManager;

    /** @var CategoryManager */
    protected $categoryManager;

    /** @var ProductCategoryManager */
    protected $productCatManager;

    /** @var UserContext */
    protected $userContext;

    /** @var VersionManager */
    protected $versionManager;

    /** @var SecurityFacade */
    protected $securityFacade;

    /** @var SequentialEditManager */
    protected $seqEditManager;

    /**
     * Constant used to redirect to the datagrid when save edit form
     * @staticvar string
     */
    const BACK_TO_GRID = 'BackGrid';

    /**
     * Constant used to redirect to create popin when save edit form
     * @staticvar string
     */
    const CREATE = 'Create';

    /**
     * Constant used to redirect to next product in a sequential edition
     * @staticvar string
     */
    const SAVE_AND_NEXT = 'SaveAndNext';

    /**
     * Constant used to redirect to the grid once all products are edited in a sequential edition
     * @staticvar string
     */
    const SAVE_AND_FINISH = 'SaveAndFinish';

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
     * @param ProductManager           $productManager
     * @param CategoryManager          $categoryManager
     * @param UserContext              $userContext
     * @param VersionManager           $versionManager
     * @param SecurityFacade           $securityFacade
     * @param ProductCategoryManager   $prodCatManager
     * @param SequentialEditManager    $seqEditManager
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
        ProductManager $productManager,
        CategoryManager $categoryManager,
        UserContext $userContext,
        VersionManager $versionManager,
        SecurityFacade $securityFacade,
        ProductCategoryManager $prodCatManager,
        SequentialEditManager $seqEditManager
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

        $this->productManager    = $productManager;
        $this->categoryManager   = $categoryManager;
        $this->userContext       = $userContext;
        $this->versionManager    = $versionManager;
        $this->securityFacade    = $securityFacade;
        $this->productCatManager = $prodCatManager;
        $this->seqEditManager    = $seqEditManager;
    }

    /**
     * Add attributes to product
     *
     * @param Request $request The request object
     * @param integer $id      The product id to which add attributes
     *
     * @AclAncestor("pim_enrich_product_add_attribute")
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
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

        $this->productManager->addAttributesToProduct($product, $availableAttributes);
        $this->addFlash('success', 'flash.product.attributes added');

        return $this->redirectToRoute('pim_enrich_product_edit', array('id' => $product->getId()));
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
        $product   = $this->findProductOr404($productId);
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
     * Find a product by its id or return a 404 response
     *
     * @param integer $id the product id
     *
     * @return \Pim\Bundle\CatalogBundle\Model\ProductInterface
     *
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
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
     * @return \Symfony\Component\Form\Form
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
}
