<?php

namespace Oro\Bundle\EntityBundle\Controller;

use Oro\Bundle\EntityBundle\Datagrid\CustomEntityDatagrid;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

use Doctrine\ORM\QueryBuilder;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Oro\Bundle\UserBundle\Annotation\Acl;

//use Oro\Bundle\EntityConfigBundle\Metadata\EntityMetadata;
//use Oro\Bundle\GridBundle\Datagrid\Datagrid;

use Oro\Bundle\EntityConfigBundle\Provider\ConfigProvider;
use Oro\Bundle\EntityConfigBundle\Entity\EntityConfigModel;

/**
 * Entities controller.
 * @Route("/entity")
 * @Acl(
 *      id="oro_entity",
 *      name="Custom entity manipulation",
 *      description="Custom entity manipulation"
 * )
 */
class EntitiesController extends Controller
{
    /**
     * Grid of Custom/Extend entity.
     * @Route(
     *      "/{id}",
     *      name="oro_entity_index",
     *      defaults={"id"=0}
     * )
     * @Acl(
     *      id="oro_entity_index",
     *      name="Grid custom entity",
     *      description="Grid custom entity",
     *      parent="oro_entity"
     * )
     * @Template()
     */
    public function indexAction(Request $request)
    {
        /** @var EntityConfigModel $entity */
        $entity = $this->getDoctrine()->getRepository(EntityConfigModel::ENTITY_NAME)->find($request->get('id'));

        /** @var ConfigProvider $extendConfigProvider */
        $extendConfigProvider = $this->get('oro_entity_config.provider.extend');
        $extendConfig         = $extendConfigProvider->getConfig($entity->getClassName());

        /** @var  CustomEntityDatagrid $datagrid */
        $datagridManager = $this->get('oro_entity.custom_datagrid.manager');

        $datagridManager->setCustomEntityClass($entity->getClassName(), $extendConfig->get('extend_class'));

        $datagridManager->setEntityName($extendConfig->get('extend_class'));
        $datagridManager->getRouteGenerator()->setRouteParameters(array('id' => $request->get('id')));

        $view = $datagridManager->getDatagrid()->createView();
        return 'json' == $this->getRequest()->getRequestFormat()
            ? $this->get('oro_grid.renderer')->renderResultsJsonResponse($view)
            : $this->render('OroEntityBundle:Entities:index.html.twig', array('datagrid' => $view));
    }

    /**
     * View custom entity instance.
     * @Route(
     *      "/view/{id}",
     *      name="oro_entity_view",
     *      defaults={"id"=0}
     * )
     * @Acl(
     *      id="oro_entity_view",
     *      name="View custom entity",
     *      description="View custom entity",
     *      parent="oro_entity"
     * )
     * @Template()
     */
    public function viewAction(Request $request)
    {

    }

    /**
     * Update custom entity instance.
     * @Route(
     *      "/update/{id}",
     *      name="oro_entity_update",
     *      defaults={"id"=0}
     * )
     * @Acl(
     *      id="oro_entity_update",
     *      name="Update custom entity",
     *      description="Update custom entity",
     *      parent="oro_entity"
     * )
     * @Template()
     */
    public function updateAction()
    {

    }

    /**
     * Delete custom entity instance.
     * @Route(
     *      "/delete/{id}",
     *      name="oro_entity_delete",
     *      defaults={"id"=0}
     * )
     * @Acl(
     *      id="oro_entity_delete",
     *      name="Delete custom entity",
     *      description="Delete custom entity",
     *      parent="oro_entity"
     * )
     * @Template()
     */
    public function deleteAction()
    {

    }
}
