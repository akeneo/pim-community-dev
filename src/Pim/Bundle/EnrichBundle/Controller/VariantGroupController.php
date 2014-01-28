<?php

namespace Pim\Bundle\EnrichBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;

use Pim\Bundle\CatalogBundle\Entity\Group;

/**
 * Variant group controller
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class VariantGroupController extends GroupController
{
    /**
     * {@inheritdoc}
     * @Template
     * @AclAncestor("pim_enrich_group_index")
     * @return Response
     */
    public function indexAction(Request $request)
    {
        return array(
            'groupTypes' => array_keys($this->groupManager->getTypeChoices(true)),
            'localeCode' => $this->userContext->getUserLocale()->getCode()
        );
    }

    /**
     * {@inheritdoc}
     *
     * @Template
     * @AclAncestor("pim_enrich_group_create")
     */
    public function createAction(Request $request)
    {
        if (!$request->isXmlHttpRequest()) {
            return $this->redirectToRoute('pim_enrich_variant_group_index');
        }

        $groupType = $this->groupManager
            ->getGroupTypeRepository()
            ->findOneBy(array('code' => 'VARIANT'));

        $group = new Group();
        $group->setType($groupType);

        if ($this->groupHandler->process($group)) {
            $this->addFlash('success', 'flash.variant group.created');

            $url = $this->generateUrl(
                'pim_enrich_variant_group_edit',
                array('id' => $group->getId())
            );
            $response = array('status' => 1, 'url' => $url);

            return new Response(json_encode($response));
        }

        return array(
            'form' => $this->groupForm->createView()
        );
    }

    /**
     * {@inheritdoc}
     *
     * @AclAncestor("pim_enrich_group_edit")
     * @Template
     */
    public function editAction(Group $group)
    {
        if ($this->groupHandler->process($group)) {
            $this->addFlash('success', 'flash.variant group.updated');
        }

        return array(
            'form'         => $this->groupForm->createView(),
            'dataLocale'   => $this->userContext->getUserLocale()->getCode(),
            'currentGroup' => $group->getId()
        );
    }
}
