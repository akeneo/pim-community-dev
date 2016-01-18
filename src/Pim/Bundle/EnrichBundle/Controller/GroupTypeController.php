<?php

namespace Pim\Bundle\EnrichBundle\Controller;

use Akeneo\Component\StorageUtils\Remover\RemoverInterface;
use Doctrine\Common\Persistence\ManagerRegistry;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Pim\Bundle\CatalogBundle\Entity\GroupType;
use Pim\Bundle\EnrichBundle\AbstractController\AbstractDoctrineController;
use Pim\Bundle\EnrichBundle\Exception\DeleteException;
use Pim\Bundle\EnrichBundle\Form\Handler\HandlerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Group type controller
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GroupTypeController extends AbstractDoctrineController
{
    /** @var HandlerInterface */
    protected $groupTypeHandler;

    /** @var Form */
    protected $groupTypeForm;

    /** @var RemoverInterface */
    protected $groupTypeRemover;

    /**
     * Constructor
     *
     * @param Request                  $request
     * @param EngineInterface          $templating
     * @param RouterInterface          $router
     * @param TokenStorageInterface    $tokenStorage
     * @param FormFactoryInterface     $formFactory
     * @param ValidatorInterface       $validator
     * @param TranslatorInterface      $translator
     * @param EventDispatcherInterface $eventDispatcher
     * @param ManagerRegistry          $doctrine
     * @param HandlerInterface         $groupTypeHandler
     * @param Form                     $groupTypeForm
     * @param RemoverInterface         $groupTypeRemover
     */
    public function __construct(
        Request $request,
        EngineInterface $templating,
        RouterInterface $router,
        TokenStorageInterface $tokenStorage,
        FormFactoryInterface $formFactory,
        ValidatorInterface $validator,
        TranslatorInterface $translator,
        EventDispatcherInterface $eventDispatcher,
        ManagerRegistry $doctrine,
        HandlerInterface $groupTypeHandler,
        Form $groupTypeForm,
        RemoverInterface $groupTypeRemover
    ) {
        parent::__construct(
            $request,
            $templating,
            $router,
            $tokenStorage,
            $formFactory,
            $validator,
            $translator,
            $eventDispatcher,
            $doctrine
        );

        $this->groupTypeHandler = $groupTypeHandler;
        $this->groupTypeForm    = $groupTypeForm;
        $this->groupTypeRemover = $groupTypeRemover;
    }

    /**
     * List group types
     *
     * @param Request $request
     *
     * @Template
     * @AclAncestor("pim_enrich_grouptype_index")
     *
     * @return Response
     */
    public function indexAction(Request $request)
    {
        return array();
    }

    /**
     * Create a group type
     *
     * @param Request $request
     *
     * @Template
     * @AclAncestor("pim_enrich_grouptype_create")
     *
     * @return Response|RedirectResponse
     */
    public function createAction(Request $request)
    {
        if (!$request->isXmlHttpRequest()) {
            return $this->redirectToRoute('pim_enrich_grouptype_index');
        }

        $groupType = new GroupType();

        if ($this->groupTypeHandler->process($groupType)) {
            $this->addFlash('success', 'flash.group type.created');

            $url = $this->generateUrl(
                'pim_enrich_grouptype_edit',
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
     * @AclAncestor("pim_enrich_grouptype_edit")
     *
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
     *
     * @param GroupType $groupType
     *
     * @AclAncestor("pim_enrich_grouptype_remove")
     *
     * @return Response|RedirectResponse
     */
    public function removeAction(GroupType $groupType)
    {
        if ($groupType->isVariant()) {
            throw new DeleteException($this->getTranslator()->trans('flash.group type.cant remove variant'));
        } elseif (count($groupType->getGroups()) > 0) {
            throw new DeleteException($this->getTranslator()->trans('flash.group type.cant remove used'));
        } else {
            $this->groupTypeRemover->remove($groupType);
        }

        if ($this->getRequest()->isXmlHttpRequest()) {
            return new Response('', 204);
        } else {
            return $this->redirectToRoute('pim_enrich_grouptype_index');
        }
    }
}
