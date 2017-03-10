<?php

namespace Pim\Bundle\EnrichBundle\Controller;

use Akeneo\Component\StorageUtils\Remover\RemoverInterface;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Pim\Bundle\CatalogBundle\Entity\GroupType;
use Pim\Bundle\EnrichBundle\Exception\DeleteException;
use Pim\Bundle\EnrichBundle\Flash\Message;
use Pim\Bundle\EnrichBundle\Form\Handler\HandlerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Group type controller
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GroupTypeController
{
    /** @var RouterInterface */
    protected $router;

    /** @var Request */
    protected $request;

    /** @var TranslatorInterface */
    protected $translator;

    /** @var HandlerInterface */
    protected $groupTypeHandler;

    /** @var Form */
    protected $groupTypeForm;

    /** @var RemoverInterface */
    protected $groupTypeRemover;

    /**
     * @param Request             $request
     * @param RouterInterface     $router
     * @param TranslatorInterface $translator
     * @param HandlerInterface    $groupTypeHandler
     * @param Form                $groupTypeForm
     * @param RemoverInterface    $groupTypeRemover
     */
    public function __construct(
        Request $request,
        RouterInterface $router,
        TranslatorInterface $translator,
        HandlerInterface $groupTypeHandler,
        Form $groupTypeForm,
        RemoverInterface $groupTypeRemover
    ) {
        $this->request = $request;
        $this->router = $router;
        $this->translator = $translator;
        $this->groupTypeHandler = $groupTypeHandler;
        $this->groupTypeForm = $groupTypeForm;
        $this->groupTypeRemover = $groupTypeRemover;
    }

    /**
     * List group types
     *
     * @Template
     * @AclAncestor("pim_enrich_grouptype_index")
     *
     * @return Response
     */
    public function indexAction(Request $request)
    {
        return [];
    }

    /**
     * Create a group type
     *
     * @param Request $request
     *
     * @Template
     * @AclAncestor("pim_enrich_grouptype_create")
     *
     * @return Response
     */
    public function createAction(Request $request)
    {
        $groupType = new GroupType();

        if ($this->groupTypeHandler->process($groupType)) {
            $this->request->getSession()->getFlashBag()->add('success', new Message('flash.group type.created'));

            $url = $this->router->generate(
                'pim_enrich_grouptype_edit',
                ['id' => $groupType->getId()]
            );
            $response = ['status' => 1, 'url' => $url];

            return new Response(json_encode($response));
        }

        return [
            'form' => $this->groupTypeForm->createView()
        ];
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
            $this->request->getSession()->getFlashBag()->add('success', new Message('flash.group type.updated'));
        }

        return [
            'form' => $this->groupTypeForm->createView(),
        ];
    }

    /**
     * Remove a group type
     *
     * @param GroupType $groupType
     *
     * @AclAncestor("pim_enrich_grouptype_remove")
     *
     * @return Response
     */
    public function removeAction(GroupType $groupType)
    {
        if ($groupType->isVariant()) {
            throw new DeleteException($this->translator->trans('flash.group type.cant remove variant'));
        } elseif (count($groupType->getGroups()) > 0) {
            throw new DeleteException($this->translator->trans('flash.group type.cant remove used'));
        } else {
            $this->groupTypeRemover->remove($groupType);
        }

        return new Response('', 204);
    }
}
