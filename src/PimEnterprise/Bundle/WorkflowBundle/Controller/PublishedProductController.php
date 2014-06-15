<?php

namespace PimEnterprise\Bundle\WorkflowBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Validator\ValidatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;

use Pim\Bundle\EnrichBundle\AbstractController\AbstractController;
use Pim\Bundle\UserBundle\Context\UserContext;
use Pim\Bundle\CatalogBundle\Manager\ProductManager;

use PimEnterprise\Bundle\WorkflowBundle\Repository\PublishedProductRepositoryInterface;
use PimEnterprise\Bundle\WorkflowBundle\Publisher\ProductPublisher;

/**
 * Published product controller
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class PublishedProductController extends AbstractController
{
    /** @var ProductManager */
    protected $manager;

    /** @var UserContext */
    protected $userContext;

    /** @var ProductPublisher */
    protected $publisher;

    /**
     * @param Request                             $request
     * @param EngineInterface                     $templating
     * @param RouterInterface                     $router
     * @param SecurityContextInterface            $securityContext
     * @param FormFactoryInterface                $formFactory
     * @param ValidatorInterface                  $validator
     * @param TranslatorInterface                 $translator
     * @param UserContext                         $userContext
     * @param ProductManager                      $productManager
     * @param PublishedProductRepositoryInterface $repository
     * @param ProductPublisher                    $publisher
     */
    public function __construct(
        Request $request,
        EngineInterface $templating,
        RouterInterface $router,
        SecurityContextInterface $securityContext,
        FormFactoryInterface $formFactory,
        ValidatorInterface $validator,
        TranslatorInterface $translator,
        UserContext $userContext,
        ProductManager $manager,
        PublishedProductRepositoryInterface $repository,
        ProductPublisher $publisher
    ) {
        parent::__construct(
            $request,
            $templating,
            $router,
            $securityContext,
            $formFactory,
            $validator,
            $translator
        );
        $this->userContext    = $userContext;
        $this->manager        = $manager;
        $this->repository     = $repository;
        $this->publisher      = $publisher;
    }

    /**
     * List of published products
     *
     * @param Request $request the request
     *
     * @AclAncestor("pimee_workflow_publishedproduct_index")
     * @Template
     * @return Response
     */
    public function indexAction(Request $request)
    {
        return array(
            'locales'    => $this->userContext->getUserLocales(),
            'dataLocale' => $this->getDataLocale(),
        );
    }

    /**
     * Publish a product
     *
     * @param Request $request
     * @param integer $id
     *
     * @Template
     * @AclAncestor("pimee_workflow_publishedproduct_index")
     * @return array
     */
    public function publishAction(Request $request, $id)
    {
        $product = $this->manager->find($id);
        $this->publisher->publish($product);
        $this->addFlash('success', 'flash.product.published');

        return $this->redirect(
            $this->generateUrl(
                'pim_enrich_product_edit',
                [
                    'id' => $product->getId()
                ]
            )
        );
    }

    /**
     * View a product
     *
     * @param Request $request
     * @param integer $id
     *
     * @Template
     * @AclAncestor("pimee_workflow_publishedproduct_index")
     * @return array
     */
    public function viewAction(Request $request, $id)
    {
        $published = $this->repository->findOneById($id);
        $locale = $this->getDataLocale();

        return [
            'published'  => $published,
            'dataLocale' => $locale,
            'locales'    => $this->userContext->getUserLocales()
        ];
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
        return $this->userContext->getCurrentLocaleCode();
    }
}
