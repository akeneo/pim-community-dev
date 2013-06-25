<?php

namespace Oro\Bundle\EntityConfigBundle\Controller;

use Oro\Bundle\EntityConfigBundle\Datagrid\FieldsDatagridManager;
use Oro\Bundle\EntityConfigBundle\Entity\ConfigField;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Oro\Bundle\GridBundle\Datagrid\Datagrid;
use Oro\Bundle\EntityConfigBundle\Datagrid\ConfigDatagridManager;
use Oro\Bundle\EntityConfigBundle\Entity\ConfigEntity;

/**
 * EntityConfig controller.
 * @Route("/oro_entityconfig")
 */
class ConfigController extends Controller
{
    /**
     * Lists all Flexible entities.
     * @Route("/", name="oro_entityconfig_index")
     * @Template()
     */
    public function indexAction(Request $request)
    {
        /** @var  ConfigDatagridManager $datagrid */
        $datagridManager = $this->get('oro_entity_config.datagrid.manager');
        $datagrid        = $datagridManager->getDatagrid();
        $view            = 'json' == $request->getRequestFormat()
            ? 'OroGridBundle:Datagrid:list.json.php'
            : 'OroEntityConfigBundle:Config:index.html.twig';

        return $this->render(
            $view,
            array(
                'buttonConfig' => $datagridManager->getLayoutActions(),
                'datagrid'     => $datagrid->createView()
            )
        );
    }

    /**
     * View Entity
     * @Route("/view/{id}", name="oro_entityconfig_view")
     * @Template()
     */
    public function viewAction($id)
    {
        $entity = $this->getDoctrine()->getRepository(ConfigEntity::ENTITY_NAME)->find($id);

        return array(
            'entity' => $entity,
        );
    }

    /**
     * @Route("/update/{id}", name="oro_entityconfig_update")
     * @Template()
     */
    public function updateAction($id)
    {
        $entity  = $this->getDoctrine()->getRepository(ConfigEntity::ENTITY_NAME)->find($id);
        $form    = $this->createForm(
            'oro_entity_config_config_entity_type',
            null,
            array('class_name' => $entity->getClassName())
        );
        $request = $this->getRequest();

        if ($request->getMethod() == 'POST') {
            $form->bind($request);

            if ($form->isValid()) {
                //persist data inside the form
                $this->get('session')->getFlashBag()->add('success', 'ConfigEntity successfully saved');

                return $this->redirect($this->generateUrl('oro_entityconfig_index'));
            }
        }

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * Lists Entity fields
     * @Route("/fields/{id}", name="oro_entityconfig_fields", requirements={"id"="\d+"}, defaults={"id"=0})
     * @Template()
     */
    public function fieldsAction($id, Request $request)
    {
        $entity  = $this->getDoctrine()->getRepository(ConfigEntity::ENTITY_NAME)->find($id);

        /** @var  FieldsDatagridManager $datagridManager */
        $datagridManager = $this->get('oro_entity_config.fieldsdatagrid.manager');
        $datagridManager->setEntityId($id);

        $datagrid = $datagridManager->getDatagrid();

        $datagridManager->getRouteGenerator()->setRouteParameters(
            array(
                'id' => $id
            )
        );

        $view = 'json' == $request->getRequestFormat()
            ? 'OroGridBundle:Datagrid:list.json.php'
            : 'OroEntityConfigBundle:Config:fields.html.twig';

        return $this->render(
            $view,
            array(
                'buttonConfig' => $datagridManager->getLayoutActions($entity),
                'datagrid'     => $datagrid->createView(),
                'entity_id'    => $id,
            )
        );
    }

    /**
     * View Field
     * @Route("/field/view/{id}", name="oro_entityconfig_field_view")
     * @Template()
     */
    public function fieldViewAction($id)
    {
        $field = $this->getDoctrine()->getRepository(ConfigField::ENTITY_NAME)->find($id);

        return array(
            'field' => $field,
        );
    }


    /**
     * @Route("/field/update/{id}", name="oro_entityconfig_field_update")
     * @Template()
     */
    public function filedUpdateAction($id)
    {
        $field   = $this->getDoctrine()->getRepository(ConfigField::ENTITY_NAME)->find($id);
        $form    = $this->createForm('oro_entity_config_config_field_type', null, array(
            'class_name' => $field->getEntity()->getClassName(),
            'field_name' => $field->getCode(),
        ));
        $request = $this->getRequest();

        if ($request->getMethod() == 'POST') {
            $form->bind($request);

            if ($form->isValid()) {
                //persist data inside the form
                $this->get('session')->getFlashBag()->add('success', 'ConfigField successfully saved');

                return $this->redirect($this->generateUrl('oro_entityconfig_fields',
                    array(
                        'id' => $field->getEntity()->getId()
                    )
                ));
            }
        }

        return array(
            'field' => $field,
            'form'  => $form->createView(),
        );
    }
}
