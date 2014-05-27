<?php

namespace PimEnterprise\Bundle\WorkflowBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Validator\ValidatorInterface;
use Doctrine\Common\Persistence\ObjectRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Oro\Bundle\SecurityBundle\SecurityFacade;

use Pim\Bundle\EnrichBundle\AbstractController\AbstractController;
use Pim\Bundle\UserBundle\Context\UserContext;
use Pim\Bundle\CatalogBundle\Manager\ProductManager;

use PimEnterprise\Bundle\WorkflowBundle\Manager\ProposalManager;
use PimEnterprise\Bundle\WorkflowBundle\Factory\PublishedProductFactory;

/**
 * Published product controller
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class PublishedProductController extends AbstractController
{
    /** @var PublishedProductFactory */
    protected $factory;

    /** @var ProductManager */
    protected $manager;

    /** @var UserContext */
    protected $userContext;

    /** @var SecurityFacade */
    protected $securityFacade;

    /**
     * @param Request                  $request
     * @param EngineInterface          $templating
     * @param RouterInterface          $router
     * @param SecurityContextInterface $securityContext
     * @param FormFactoryInterface     $formFactory
     * @param ValidatorInterface       $validator
     * @param TranslatorInterface      $translator
     * @param UserContext              $userContext
     * @param SecurityFacade           $securityFacade
     * @param PublishedProductFactory  $factory
     * @param ProductManager           $productManager
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
        SecurityFacade $securityFacade,
        PublishedProductFactory $factory,
        ProductManager $manager
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
        $this->securityFacade = $securityFacade;
        $this->factory        = $factory;
        $this->manager        = $manager;
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
     * @param string  $locale
     *
     * @Template
     * TODO : AclAncestor("pimee_workflow_publishedproduct_publish")
     * @return array
     */
    public function publishAction(Request $request, $id, $locale)
    {
        $product = $this->manager->find($id);

        $published = $this->factory->createPublishedProduct($product);

        $this->manager->getObjectManager()->persist($published);
        $this->manager->getObjectManager()->flush();

        // var_dump($published->getId());
        $this->addFlash('success', 'flash.product.published', ['%locale%' => $locale]);

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
