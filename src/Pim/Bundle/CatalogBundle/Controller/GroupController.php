<?php

namespace Pim\Bundle\CatalogBundle\Controller;

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

use Pim\Bundle\CatalogBundle\AbstractController\AbstractController;
use Pim\Bundle\CatalogBundle\Entity\Group;
use Pim\Bundle\CatalogBundle\Form\Handler\GroupHandler;
use Pim\Bundle\CatalogBundle\Manager\GroupManager;
use Pim\Bundle\CatalogBundle\Manager\LocaleManager;

/**
 * Group controller
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GroupController extends AbstractController
{
    /**
     * @staticvar integer The maximum number of group products to be displayed
     */
    const MAX_PRODUCTS = 5;

    /**
     * @var GroupManager
     */
    protected $groupManager;

    /**
     * @var GroupHandler
     */
    protected $groupHandler;

    /**
     * @var Form
     */
    protected $groupForm;

    /**
     * @var LocaleManager
     */
    protected $localeManager;

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
     * @param GroupManager             $groupManager
     * @param GroupHandler             $groupHandler
     * @param Form                     $groupForm
     * @param LocaleManager            $localeManager
     */
    public function __construct(
        Request $request,
        EngineInterface $templating,
        RouterInterface $router,
        SecurityContextInterface $securityContext,
        FormFactoryInterface $formFactory,
        ValidatorInterface $validator,
        TranslatorInterface $translator,
        GroupManager $groupManager,
        GroupHandler $groupHandler,
        Form $groupForm,
        LocaleManager $localeManager
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

        $this->groupManager  = $groupManager;
        $this->groupHandler  = $groupHandler;
        $this->groupForm     = $groupForm;
        $this->localeManager = $localeManager;
    }

    /**
     * List groups
     *
     * @param Request $request
     *
     * @Template
     * @AclAncestor("pim_catalog_group_index")
     * @return Response
     */
    public function indexAction(Request $request)
    {
        return [
            'groupTypes' => array_keys($this->groupManager->getTypeChoices(false)),
            'localeCode' => $this->localeManager->getUserLocale()->getCode()
        ];
    }

    /**
     * Create a group
     * @param Request $request
     *
     * @Template
     * @AclAncestor("pim_catalog_group_create")
     * @return Response|RedirectResponse
     */
    public function createAction(Request $request)
    {
        if (!$request->isXmlHttpRequest()) {
            return $this->redirectToRoute('pim_catalog_group_index');
        }

        $group = new Group();

        if ($this->groupHandler->process($group)) {
            $this->addFlash('success', 'flash.group.created');

            $url = $this->generateUrl(
                'pim_catalog_group_edit',
                ['id' => $group->getId()]
            );
            $response = ['status' => 1, 'url' => $url];

            return new Response(json_encode($response));
        }

        return [
            'form' => $this->groupForm->createView()
        ];
    }

    /**
     * Edit a group
     *
     * @param Group $group
     *
     * @Template
     * @AclAncestor("pim_catalog_group_edit")
     * @return array
     */
    public function editAction(Group $group)
    {
        if ($this->groupHandler->process($group)) {
            $this->addFlash('success', 'flash.group.updated');
        }

        return [
            'form'         => $this->groupForm->createView(),
            'dataLocale'   => $this->localeManager->getUserLocale()->getCode(),
            'currentGroup' => $group->getId()
        ];
    }

    /**
     * Remove a group
     * @param Group $group
     *
     * @AclAncestor("pim_catalog_group_remove")
     * @return Response|RedirectResponse
     */
    public function removeAction(Group $group)
    {
        $this->groupManager->remove($group);

        if ($this->getRequest()->isXmlHttpRequest()) {
            return new Response('', 204);
        } else {
            return $this->redirectToRoute('pim_catalog_group_index');
        }
    }

    /**
     * Display the products of a group
     *
     * @param Group $group
     *
     * @return array
     *
     * @AclAncestor("pim_catalog_product_edit")
     * @Template("PimCatalogBundle:Group:_productList.html.twig")
     */
    public function productListAction(Group $group)
    {
        return $this->groupManager->getProductList($group, static::MAX_PRODUCTS);
    }

    /**
     * History of a group
     *
     * @param Group $group
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|template
     */
    public function historyAction(Group $group)
    {
        return $this->render(
            'PimCatalogBundle:Group:_history.html.twig',
            [
                'group' => $group
            ]
        );
    }
}
