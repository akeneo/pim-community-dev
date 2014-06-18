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

use PimEnterprise\Bundle\WorkflowBundle\Manager\PublishedProductManager;

/**
 * Published product controller
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class PublishedProductController extends AbstractController
{
    /** @var UserContext */
    protected $userContext;

    /** @var PublishedProductManager */
    protected $manager;

    /**
     * @param Request                  $request
     * @param EngineInterface          $templating
     * @param RouterInterface          $router
     * @param SecurityContextInterface $securityContext
     * @param FormFactoryInterface     $formFactory
     * @param ValidatorInterface       $validator
     * @param TranslatorInterface      $translator
     * @param UserContext              $userContext
     * @param PublishedProductManager  $manager
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
        PublishedProductManager $manager
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
    }

    /**
     * List of published products
     *
     * @param Request $request the request
     *
     * @AclAncestor("pimee_workflow_published_product_index")
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
     * @AclAncestor("pimee_workflow_published_product_index")
     * @return array
     */
    public function publishAction(Request $request, $id)
    {
        $product = $this->manager->findOriginalProduct($id);
        $this->manager->publish($product);
        $this->addFlash('success', 'flash.product.published');

        if (!isset($parameters['dataLocale'])) {
            $parameters['dataLocale'] = $this->getDataLocale();
        }

        return parent::redirectToRoute(
            'pim_enrich_product_edit',
            ['id' => $product->getId(), 'dataLocale' => $this->getDataLocale()]
        );
    }

    /**
     * View a product
     *
     * @param Request $request
     * @param integer $id
     *
     * @Template
     * @AclAncestor("pimee_workflow_published_product_index")
     * @return array
     */
    public function viewAction(Request $request, $id)
    {
        $published = $this->manager->findPublishedProductById($id);
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
