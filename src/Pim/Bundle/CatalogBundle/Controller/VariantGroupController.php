<?php

namespace Pim\Bundle\CatalogBundle\Controller;

use Pim\Bundle\CatalogBundle\Entity\GroupType;

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
     * @AclAncestor("pim_catalog_group_index")
     */
    public function indexAction(Request $request)
    {
        /** @var QueryBuilder $queryBuilder */
        $queryBuilder = $this->getManager()->createQueryBuilder();
        $datagrid = $this->datagridWorker->getDatagrid('variant_group', $queryBuilder);

        $view = ('json' === $request->getRequestFormat())
            ? 'OroGridBundle:Datagrid:list.json.php'
            : 'PimCatalogBundle:VariantGroup:index.html.twig';

        return $this->render($view, array('datagrid' => $datagrid->createView()));
    }

    /**
     * {@inheritdoc}
     *
     * @Template
     * @AclAncestor("pim_catalog_group_create")
     */
    public function createAction(Request $request)
    {
        if (!$request->isXmlHttpRequest()) {
            return $this->redirectToRoute('pim_catalog_variant_group_index');
        }

        $groupType = $this
            ->getRepository('PimCatalogBundle:GroupType')
            ->findOneBy(array('code' => 'VARIANT'));

        $group = new Group();
        $group->setType($groupType);

        if ($this->groupHandler->process($group)) {
            $this->addFlash('success', 'flash.variant group.created');

            $url = $this->generateUrl(
                'pim_catalog_variant_group_edit',
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
     * @AclAncestor("pim_catalog_group_edit")
     * @Template("PimCatalogBundle:Group:edit.html.twig")
     */
    public function editAction(Group $group)
    {
        if ($this->groupHandler->process($group)) {
            $this->addFlash('success', 'flash.variant group.updated');
        }

        $datagridManager = $this->datagridWorker->getDatagridManager('group_product');
        $datagridManager->setGroup($group);
        $datagridView = $datagridManager->getDatagrid()->createView();

        if ('json' === $this->getRequest()->getRequestFormat()) {
            return $this->render(
                'OroGridBundle:Datagrid:list.json.php',
                array('datagrid' => $datagridView)
            );
        }

        return array(
            'form' => $this->groupForm->createView(),
            'datagrid' => $datagridView
        );
    }
}
