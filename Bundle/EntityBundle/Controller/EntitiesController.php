<?php

namespace Oro\Bundle\EntityBundle\Controller;

use Doctrine\ORM\QueryBuilder;
use Oro\Bundle\EntityConfigBundle\Metadata\EntityMetadata;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Oro\Bundle\UserBundle\Annotation\Acl;
use Oro\Bundle\GridBundle\Datagrid\Datagrid;

use Oro\Bundle\EntityConfigBundle\Provider\ConfigProvider;

use Oro\Bundle\EntityConfigBundle\Datagrid\EntityFieldsDatagridManager;
use Oro\Bundle\EntityConfigBundle\Datagrid\ConfigDatagridManager;

use Oro\Bundle\EntityConfigBundle\Entity\FieldConfigModel;
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
     * Lists all Flexible entities.
     * @Route(
     *      "/{id}",
     *      name="oro_entity_index",
     *      defaults={"id"=0}
     * )
     * @Acl(
     *      id="oro_entity_index",
     *      name="View custom entity",
     *      description="View custom entity",
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

//        var_dump($entity->getClassName());
//        var_dump($extendConfig->get('extend_class'));
//        var_dump($extendConfig);
//        die;

        /** @var  ConfigDatagridManager $datagrid */
        $datagridManager = $this->get('oro_entity.custom_datagrid.manager');

        $datagrid = $datagridManager->getDatagrid();

        $datagrid->getRouteGenerator()->setRouteParameters(array('id' => $request->get('id')));
        $datagrid->setEntityName($extendConfig->get('extend_class'));


        var_dump($datagrid->getRouteGenerator());



        var_dump($datagrid->getEntityName());
        die;

        $view            = 'json' == $request->getRequestFormat()
            ? 'OroGridBundle:Datagrid:list.json.php'
            : 'OroEntityBundle:Entities:index.html.twig';

        return $this->render(
            $view,
            array(
                //'buttonConfig' => $datagridManager->getLayoutActions(),
                'datagrid'     => $datagrid->createView()
            )
        );
    }

    public function viewAction(Request $request)
    {

    }
}
