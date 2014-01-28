<?php

namespace Pim\Bundle\EnrichBundle\Controller;

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

use Pim\Bundle\EnrichBundle\AbstractController\AbstractDoctrineController;
use Pim\Bundle\CatalogBundle\Entity\GroupType;
use Pim\Bundle\UserBundle\Context\UserContext;
use Pim\Bundle\EnrichBundle\Exception\DeleteException;
use Pim\Bundle\EnrichBundle\Form\Handler\GroupTypeHandler;

/**
 * Group type controller
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GroupTypeController extends AbstractDoctrineController
{
    /**
     * @var UserContext
     */
    protected $userContext;

    /**
     * @var GroupTypeHandler
     */
    protected $groupTypeHandler;

    /**
     * @var Form
     */
    protected $groupTypeForm;

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
     * @param UserContext              $userContext
     * @param GroupTypeHandler         $groupTypeHandler
     * @param Form                     $groupTypeForm
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
        UserContext $userContext,
        GroupTypeHandler $groupTypeHandler,
        Form $groupTypeForm
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

        $this->userContext      = $userContext;
        $this->groupTypeHandler = $groupTypeHandler;
        $this->groupTypeForm    = $groupTypeForm;
    }

    /**
     * List group types
     *
     * @param Request $request
     *
     * @Template
     * @AclAncestor("pim_enrich_group_type_index")
     * @return Response
     */
    public function indexAction(Request $request)
    {
        return array(
            'localeCode' => $this->userContext->getUserLocale()->getCode()
        );
    }

    /**
     * Create a group type
     * @param Request $request
     *
     * @Template
     * @AclAncestor("pim_enrich_group_type_create")
     * @return Response|RedirectResponse
     */
    public function createAction(Request $request)
    {
        if (!$request->isXmlHttpRequest()) {
            return $this->redirectToRoute('pim_enrich_group_type_index');
        }

        $groupType = new GroupType();

        if ($this->groupTypeHandler->process($groupType)) {
            $this->addFlash('success', 'flash.group type.created');

            $url = $this->generateUrl(
                'pim_enrich_group_type_edit',
                array('id' => $groupType->getId())
            );
            $response = array('status' => 1, 'url' => $url);

            return new Response(json_encode($response));
        }

        return array(
            'form' => $this->groupTypeForm->createView()
        );
    }

    /**
     * Edit a group type
     *
     * @param GroupType $groupType
     *
     * @Template
     * @AclAncestor("pim_enrich_group_type_edit")
     * @return array
     */
    public function editAction(GroupType $groupType)
    {
        if ($this->groupTypeHandler->process($groupType)) {
            $this->addFlash('success', 'flash.group type.updated');
        }

        return array(
            'form' => $this->groupTypeForm->createView(),
        );
    }

    /**
     * Remove a group type
     * @param GroupType $groupType
     *
     * @AclAncestor("pim_enrich_group_type_remove")
     * @return Response|RedirectResponse
     */
    public function removeAction(GroupType $groupType)
    {
        if ($groupType->isVariant()) {
            throw new DeleteException($this->getTranslator()->trans('flash.group type.cant remove variant'));
        } elseif (count($groupType->getGroups()) > 0) {
            throw new DeleteException($this->getTranslator()->trans('flash.group type.cant remove used'));
        } else {
            $this->getManager()->remove($groupType);
            $this->getManager()->flush();
        }

        if ($this->getRequest()->isXmlHttpRequest()) {
            return new Response('', 204);
        } else {
            return $this->redirectToRoute('pim_enrich_group_type_index');
        }
    }
}
