<?php

namespace Pim\Bundle\CatalogBundle\Controller;

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

use Pim\Bundle\CatalogBundle\AbstractController\AbstractDoctrineController;
use Pim\Bundle\CatalogBundle\Datagrid\DatagridWorkerInterface;
use Pim\Bundle\CatalogBundle\Entity\Group;
use Pim\Bundle\CatalogBundle\Entity\GroupType;
use Pim\Bundle\CatalogBundle\Form\Handler\GroupHandler;

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
     *
     * @Template
     * @AclAncestor("pim_catalog_group_create")
     */
    public function createAction(Request $request)
    {
        return parent::createAction($request);
    }

    /**
     * {@inheritdoc}
     *
     * @AclAncestor("pim_catalog_group_edit")
     * @Template
     */
    public function editAction(Group $group)
    {
        return parent::editAction($group);
    }

    /**
     * {@inheritdoc}
     */
    protected function createGroup()
    {
        $groupType = $this
            ->getRepository('PimCatalogBundle:GroupType')
            ->findOneBy(array('code' => 'VARIANT'));

        $group = new Group();
        $group->setType($groupType);

        return $group;
    }

    /**
     * {@inheritdoc}
     */
    protected function getGroupDatagrid()
    {
        $queryBuilder = $this->getManager()->createQueryBuilder();

        return $this->datagridWorker->getDatagrid('variant_group', $queryBuilder);
    }

    /**
     * {@inheritdoc}
     */
    protected function redirectToIndex()
    {
        return $this->redirectToRoute('pim_catalog_variant_group_index');
    }

    /**
     * {@inheritdoc}
     */
    protected function successCreate(Group $group)
    {
        $this->addFlash('success', 'flash.variant group.created');

        $url = $this->generateUrl(
            'pim_catalog_variant_group_edit',
            array('id' => $group->getId())
        );
        $response = array('status' => 1, 'url' => $url);

        return new Response(json_encode($response));
    }

    /**
     * {@inheritdoc}
     */
    protected function processGroupHandler(Group $group)
    {
        if ($this->groupHandler->process($group)) {
            $this->addFlash('success', 'flash.variant group.updated');
        }
    }
}
