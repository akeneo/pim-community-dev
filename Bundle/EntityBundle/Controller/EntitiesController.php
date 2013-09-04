<?php

namespace Oro\Bundle\EntityBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\EntityManager;

use FOS\Rest\Util\Codes;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Oro\Bundle\UserBundle\Annotation\Acl;
use Oro\Bundle\EntityBundle\Datagrid\CustomEntityDatagrid;
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

        /** @var ConfigProvider $entityConfigProvider */
        $entityConfigProvider = $this->get('oro_entity_config.provider.entity');
        $entityConfig         = $entityConfigProvider->getConfig($entity->getClassName());

        /** @var  CustomEntityDatagrid $datagrid */
        $datagridManager = $this->get('oro_entity.custom_datagrid.manager');

        $className = $entity->getClassName();

        $datagridManager->setCustomEntityClass($className, $extendConfig->get('extend_class'));
        $datagridManager->setParent($entity->getId());
        $datagridManager->setEntityName($extendConfig->get('extend_class'));
        $datagridManager->getRouteGenerator()->setRouteParameters(array('id' => $request->get('id')));

        $view = $datagridManager->getDatagrid()->createView();

        return 'json' == $this->getRequest()->getRequestFormat()
            ? $this->get('oro_grid.renderer')->renderResultsJsonResponse($view)
            : $this->render(
                'OroEntityBundle:Entities:index.html.twig',
                array(
                    'datagrid'  => $view,
                    'entity_id' => $request->get('id'),
                    'label'     => $entityConfig->get('label')
                )
            );
    }

    /**
     * View custom entity instance.
     * @Route(
     *      "/view/{entity_id}/item/{id}",
     *      name="oro_entity_view",
     *      defaults={"entity_id"=0, "id"=0}
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
     *      "/update/{entity_id}/item/{id}",
     *      name="oro_entity_update",
     *      defaults={"entity_id"=0, "id"=0}
     * )
     * @Acl(
     *      id="oro_entity_update",
     *      name="Update custom entity",
     *      description="Update custom entity",
     *      parent="oro_entity"
     * )
     * @Template()
     */
    public function updateAction(Request $request, $entity_id, $id)
    {
        /** @var EntityConfigModel $entity */
        $entity = $this->getDoctrine()->getRepository(EntityConfigModel::ENTITY_NAME)->find($entity_id);

        /** @var ConfigProvider $extendConfigProvider */
        $extendConfigProvider = $this->get('oro_entity_config.provider.extend');
        $extendConfig         = $extendConfigProvider->getConfig($entity->getClassName());

        /** @var ConfigProvider $entityConfigProvider */
        $entityConfigProvider = $this->get('oro_entity_config.provider.entity');
        $entityConfig         = $entityConfigProvider->getConfig($entity->getClassName());

        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManagerForClass($extendConfig->get('extend_class'));

        $class = $extendConfig->get('extend_class');
        if (!$id) {
            $record = new $class;
        } else {
            $record = $em->find($class, $id);
        }

        $form = $this->createForm(
            'custom_entity_type',
            $record,
            array(
                'className' => $entity->getClassName(),
            )
        );

        if ($request->getMethod() == 'POST') {
            $form->submit($request);

            if ($form->isValid()) {

                $em->persist($record);
                $em->flush();

                $id = $record->getId();

                $this->get('session')->getFlashBag()->add('success', 'Entity successfully saved');
                return $this->get('oro_ui.router')->actionRedirect(
                    array(
                        'route'      => 'oro_entity_update',
                        'parameters' => array(
                            'entity_id' => $entity_id,
                            'id' => $id
                        ),
                    ),
                    array(
                        'route' => 'oro_entity_index',
                        'parameters' => array(
                            'id' => $entity_id,
                        )
                    )
                );
            }
        }

        return array(
            'record' => $record,
            'entity_id' => $entity_id,
            'entity_config' => $entityConfig,
            'form' => $form->createView(),
        );
    }

    /**
     * Delete custom entity instance.
     * @Route(
     *      "/delete/{entity_id}/item/{id}",
     *      name="oro_entity_delete",
     *      defaults={"entity_id"=0, "id"=0}
     * )
     * @Acl(
     *      id="oro_entity_delete",
     *      name="Delete custom entity",
     *      description="Delete custom entity",
     *      parent="oro_entity"
     * )
     */
    public function deleteAction(Request $request)
    {
        /** @var EntityConfigModel $entity */
        $entity = $this->getDoctrine()->getRepository(EntityConfigModel::ENTITY_NAME)->find($request->get('entity_id'));

        /** @var ConfigProvider $extendConfigProvider */
        $extendConfigProvider = $this->get('oro_entity_config.provider.extend');
        $extendConfig         = $extendConfigProvider->getConfig($entity->getClassName());

        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManagerForClass($extendConfig->get('extend_class'));

        $record = $em->find($extendConfig->get('extend_class'), $request->get('id'));

        if (!$record) {
            return new JsonResponse('', Codes::HTTP_FORBIDDEN);
        }

        $em->remove($record);
        $em->flush();

        return new JsonResponse('', Codes::HTTP_OK);
    }
}
